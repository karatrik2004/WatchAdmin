<?php
namespace App\Services;

use Carbon\Carbon;
use App\Models\XeroToken;
use Exception;
use Illuminate\Support\Facades\Http;
use XeroAPI\XeroPHP\Api\AccountingApi;
use XeroAPI\XeroPHP\Configuration;
use XeroAPI\XeroPHP\Models\Accounting\Contact;
use XeroAPI\XeroPHP\Models\Accounting\Contacts;
use XeroAPI\XeroPHP\Models\Accounting\Phone;
use XeroAPI\XeroPHP\Models\Accounting\Address;
use XeroAPI\XeroPHP\Models\Accounting\Invoice;
use XeroAPI\XeroPHP\Models\Accounting\Invoices;
use XeroAPI\XeroPHP\Models\Accounting\LineAmountTypes;
use XeroAPI\XeroPHP\Models\Accounting\LineItem;
use GuzzleHttp\Client;


class XeroService
{
    protected string $tenantId;

    /** @var string|null Cached org base currency from GET /Organisation (per request lifecycle). */
    protected static ?string $organisationBaseCurrency = null;

    /**
     * Per-request memo for {@see tryGetLiveCurrenciesRate} (avoids duplicate GET /Currencies when invoice fallback runs).
     *
     * @var array<string, float|null>
     */
    private array $liveCurrenciesRateCache = [];

    public function __construct()
    {
        $this->tenantId = (string) (env('XERO_TENANT_ID') ?: '');
    }

    /**
     * Xero sometimes returns AUD/USD as ~0.72 (USD per AUD) instead of ~1.39 (AUD per USD).
     * Stored value is always "base per 1 unit of $code" (AUD per 1 USD when base is AUD).
     *
     * Xero's Exchange rates table rounds "Units per AUD" to 6 dp and "AUD per Unit" to 5 dp as exact reciprocals.
     * We mirror that so 1/0.716764 does not become 1.39516 while the UI shows 1.39432.
     */
    private function xeroCurrencyRateAsBasePerForeignUnit(string $base, string $code, float $raw): float
    {
        if ($raw <= 0) {
            return 0.0;
        }
        $base = strtoupper($base);
        $code = strtoupper(trim($code));
        if ($base === 'AUD' && $code === 'USD') {
            if ($raw >= 1.05 && $raw <= 2.5) {
                return round($raw, 5);
            }
            if ($raw >= 0.55 && $raw < 1.05) {
                $unitsPerAud = round($raw, 6);

                return round(1.0 / $unitsPerAud, 5);
            }
        }
        if ($base === 'USD' && $code === 'AUD') {
            if ($raw >= 0.55 && $raw <= 1.05) {
                return round($raw, 6);
            }
            if ($raw >= 1.05 && $raw <= 2.5) {
                $audPerUsd = round($raw, 5);

                return round(1.0 / $audPerUsd, 6);
            }
        }

        return $raw;
    }

    /**
     * Align with Xero's Exchange rates grid when org base is AUD and USD is involved:
     * "Units per AUD" (AUD→USD) is shown to 6 dp; "AUD per Unit" (USD→AUD) to 5 dp — avoids float drift from 1/x.
     */
    private function roundRateToMatchXeroAudUsdTable(string $base, string $from, string $to, float $rate): float
    {
        $base = strtoupper($base);
        $from = strtoupper(trim($from));
        $to = strtoupper(trim($to));
        if ($base !== 'AUD' || ($from !== 'USD' && $to !== 'USD')) {
            return $rate;
        }
        if ($from === 'AUD' && $to === 'USD') {
            return round($rate, 6);
        }
        if ($from === 'USD' && $to === 'AUD') {
            return round($rate, 5);
        }

        return $rate;
    }

    /**
     * Normalise Xero {@see CurrencyRate} to "organisation base units per 1 unit of $code"
     * for use in {@see tryGetLiveCurrenciesRate} (same semantics as invoice CurrencyRate).
     */
    private function normalizedOrgCurrencyRate(string $base, string $code, float $raw): ?float
    {
        if ($raw <= 0 || abs($raw - 1.0) < 0.000001) {
            return null;
        }
        $normalized = $this->xeroCurrencyRateAsBasePerForeignUnit($base, $code, $raw);
        if ($normalized <= 0 || abs($normalized - 1.0) < 0.000001) {
            return null;
        }

        return $normalized;
    }

    private function normalizeCurrencyCode(string $code): string
    {
        return strtoupper(trim($code));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchInvoicesForCurrency(string $accessToken, string $searchCurrency, ?string $statuses, ?\DateTime $date = null): array
    {
        $utcDate = $this->normalizeToUtcDate($date);
        $url = 'https://api.xero.com/api.xro/2.0/Invoices';
        $where = 'CurrencyCode=="' . $searchCurrency . '"';
        if ($utcDate !== null) {
            $where .= '&&Date<=DateTime('
                . $utcDate->format('Y') . ','
                . $utcDate->format('n') . ','
                . $utcDate->format('j')
                . ')';
        }
        $params = [
            'where' => $where,
            'order' => 'Date DESC',
            'page' => 1,
            'pageSize' => 50,
        ];
        if ($statuses !== null && $statuses !== '') {
            $params['Statuses'] = $statuses;
        }
        
        $resp = Http::withHeaders($this->xeroAccountingRequestHeaders($accessToken))
            ->timeout(20)
            ->get($url, $params);

        if (!$resp->ok()) {
            \Log::warning('Xero Invoices list failed for FX', [
                'status' => $resp->status(),
                'currency' => $searchCurrency,
                'statuses_filter' => $statuses,
                'utc_date' => $utcDate ? $utcDate->format('Y-m-d') : null,
            ]);

            return [];
        }

        $invoices = $resp->json()['Invoices'] ?? [];
        if ($utcDate === null) {
            return $invoices;
        }

        // Keep a strict UTC cutoff in PHP in addition to Xero where-clause date filtering.
        $utcCutoffDate = $utcDate->format('Y-m-d');

        return array_values(array_filter($invoices, static function (array $inv) use ($utcCutoffDate): bool {
            $invoiceDate = substr((string)($inv['DateString'] ?? ''), 0, 10);
            if ($invoiceDate === '') {
                return true;
            }

            return $invoiceDate <= $utcCutoffDate;
        }));
    }

    private function normalizeToUtcDate(?\DateTime $date): ?Carbon
    {
        if ($date === null) {
            return null;
        }

        // Xero invoice Date filters are calendar-day based; keep the provided day stable.
        // Converting timezone first can shift the day backward/forward unexpectedly.
        $ymd = Carbon::instance($date)->format('Y-m-d');

        return Carbon::createFromFormat('Y-m-d H:i:s', $ymd . ' 00:00:00', 'UTC');
    }

    /**
     * Headers for Xero Accounting API calls — avoid stale intermediaries; each request asks Xero fresh data.
     *
     * @return array<string, string>
     */
    private function xeroAccountingRequestHeaders(string $accessToken): array
    {
        return [
            'Authorization' => 'Bearer ' . $accessToken,
            'Xero-tenant-id' => $this->tenantId,
            'Accept' => 'application/json',
            'Cache-Control' => 'no-cache',
            'Pragma' => 'no-cache',
        ];
    }

    /**
     * Merge per-code GET /Currencies row into {@see $rates} using base-per-foreign semantics
     * (aligned with invoice CurrencyRate — see {@see xeroCurrencyRateAsBasePerForeignUnit}).
     */
    private function mergeLatestCurrencyRowRaw(string $accessToken, string $base, string $code, array &$rates): void
    {
        $code = $this->normalizeCurrencyCode($code);
        $base = $this->normalizeCurrencyCode($base);
        if ($code === '' || $code === $base) {
            return;
        }

        $url = 'https://api.xero.com/api.xro/2.0/Currencies';
        $resp = Http::withHeaders($this->xeroAccountingRequestHeaders($accessToken))
            ->timeout(10)
            ->get($url, ['where' => 'Code=="' . $code . '"']);

        if (!$resp->ok()) {
            return;
        }

        $json = $resp->json();
        $rows = is_array($json) ? ($json['Currencies'] ?? []) : [];
        $bestRow = $this->selectBestCurrencyRowForLiveRate($rows, $code);
        if ($bestRow === null) {
            return;
        }

        $currencyRateRaw = (float)($bestRow['CurrencyRate'] ?? 0);
        $normalized = $this->normalizedOrgCurrencyRate($base, $code, $currencyRateRaw);
        if ($normalized === null) {
            return;
        }
        $rates[$code] = $normalized;
    }

    /**
     * Prefer rows with a real CurrencyRate (not placeholder 1:1), then newest timestamp, then later index.
     *
     * @param array<int, array<string, mixed>> $currencies
     */
    private function selectBestCurrencyRowForLiveRate(array $currencies, string $code): ?array
    {
        $code = $this->normalizeCurrencyCode($code);
        $candidates = [];

        foreach ($currencies as $idx => $currency) {
            $rowCode = $this->normalizeCurrencyCode((string)($currency['Code'] ?? ''));
            if ($rowCode !== $code) {
                continue;
            }

            $currencyRateRaw = (float)($currency['CurrencyRate'] ?? 0);
            if ($currencyRateRaw <= 0 || abs($currencyRateRaw - 1.0) < 0.000001) {
                continue;
            }

            $candidates[] = [
                'row' => $currency,
                'idx' => $idx,
                'ts' => $this->extractXeroRowTimestamp($currency),
            ];
        }

        if ($candidates === []) {
            return null;
        }

        usort($candidates, static function (array $a, array $b): int {
            if ($a['ts'] !== $b['ts']) {
                return $b['ts'] <=> $a['ts'];
            }

            return $b['idx'] <=> $a['idx'];
        });

        return $candidates[0]['row'];
    }

    /**
     * Convert known Xero datetime fields to epoch seconds.
     * Supports /Date(1709251200000+0000)/ and regular datetime strings.
     *
     * @param array<string, mixed> $row
     */
    private function extractXeroRowTimestamp(array $row): int
    {
        foreach (['UpdatedDateUTC', 'ModifiedDateUTC', 'Date', 'EffectiveDate'] as $field) {
            $raw = (string)($row[$field] ?? '');
            if ($raw === '') {
                continue;
            }

            if (preg_match('/\/Date\((\d+)(?:[+-]\d+)?\)\//', $raw, $m) === 1) {
                return (int) floor(((int) $m[1]) / 1000);
            }

            $ts = strtotime($raw);
            if ($ts !== false) {
                return (int) $ts;
            }
        }

        return 0;
    }

    /**
     * Organisation base (home) currency from Xero, with config fallback if the API is unavailable.
     * Aligns FX math with the connected tenant so XERO_BASE_CURRENCY cannot silently desync from Xero.
     */
    public function getOrganisationBaseCurrency(): string
    {
        if (self::$organisationBaseCurrency !== null) {
            return self::$organisationBaseCurrency;
        }

        $fallback = $this->normalizeCurrencyCode((string) env('XERO_BASE_CURRENCY', 'AUD'));

        try {
            $accessToken = $this->getAccessToken();
            if ($accessToken && $this->tenantId) {
                $resp = Http::withHeaders($this->xeroAccountingRequestHeaders($accessToken))
                    ->timeout(10)
                    ->get('https://api.xero.com/api.xro/2.0/Organisation');

                if ($resp->ok()) {
                    $orgs = $resp->json()['Organisations'] ?? [];
                    $code = $this->normalizeCurrencyCode((string) (($orgs[0] ?? [])['BaseCurrency'] ?? ''));
                    if ($code !== '') {
                        self::$organisationBaseCurrency = $code;

                        return self::$organisationBaseCurrency;
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Xero Organisation BaseCurrency fetch failed', ['message' => $e->getMessage()]);
        }

        self::$organisationBaseCurrency = $fallback;

        return self::$organisationBaseCurrency;
    }

    private function tryGetInvoiceRate(
        string $from,
        string $to,
        string $base,
        ?\DateTime $date,
        string $successLog,
        string $failureLog
    ): ?float {
        try {
            $conv = $this->getXeroConversionRate($from, $to, $date);
            //dd($conv, $base, $from, $to,$date);
            $payload = ['from' => $from, 'to' => $to, 'rate' => $conv['rate']];
            if ($date !== null) {
                $payload['date'] = $date->format('Y-m-d');
            }
            if (isset($conv['source'])) {
                $payload['source'] = $conv['source'];
            }
            \Log::info($successLog, $payload);

            return $this->roundRateToMatchXeroAudUsdTable($base, $from, $to, (float) $conv['rate']);
        } catch (\Exception $ex) {
            $payload = ['from' => $from, 'to' => $to, 'message' => $ex->getMessage()];
            if ($date !== null) {
                $payload['date'] = $date->format('Y-m-d');
            }
            \Log::debug($failureLog, $payload);
        }

        return null;
    }

    private function tryGetLiveCurrenciesRate(string $from, string $to, string $base): ?float
    {
        $from = $this->normalizeCurrencyCode($from);
        $to = $this->normalizeCurrencyCode($to);
        $base = $this->normalizeCurrencyCode($base);

        $cacheKey = $from . '|' . $to . '|' . $base;
        if (array_key_exists($cacheKey, $this->liveCurrenciesRateCache)) {
            return $this->liveCurrenciesRateCache[$cacheKey];
        }

        // Primary: Xero Currencies API — CurrencyRate = organisation base units per 1 unit of foreign currency.
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken || trim($this->tenantId) === '') {
                \Log::warning('Xero Currencies skipped: missing access token or tenant id');

                $this->liveCurrenciesRateCache[$cacheKey] = null;

                return null;
            }

            $url = 'https://api.xero.com/api.xro/2.0/Currencies';
            $rates = [$base => 1.0];
            $allRows = [];

            $resp = Http::withHeaders($this->xeroAccountingRequestHeaders($accessToken))
                ->timeout(15)
                ->get($url);

            if ($resp->ok()) {
                $json = $resp->json();
                $allRows = is_array($json) ? ($json['Currencies'] ?? []) : [];

                foreach ($allRows as $currency) {
                    $code = $this->normalizeCurrencyCode((string)($currency['Code'] ?? ''));
                    if ($code === '' || $code === $base) {
                        continue;
                    }
                    $currencyRateRaw = (float)($currency['CurrencyRate'] ?? 0);
                    $normalized = $this->normalizedOrgCurrencyRate($base, $code, $currencyRateRaw);
                    if ($normalized !== null) {
                        $rates[$code] = $normalized;
                    }
                }
            } else {
                \Log::warning('Xero Currencies API not ok', [
                    'status' => $resp->status(),
                    'snippet' => substr((string) $resp->body(), 0, 400),
                ]);
            }

            // Per-code GET — preferred source when full list is incomplete or stale.
            foreach (array_unique([$from, $to]) as $leg) {
                $this->mergeLatestCurrencyRowRaw($accessToken, $base, $leg, $rates);
            }

            // Fill any missing leg from full response using best valid row (handles duplicate Code rows).
            foreach (array_unique([$from, $to]) as $leg) {
                if ($leg === $base || isset($rates[$leg])) {
                    continue;
                }
                $bestRow = $this->selectBestCurrencyRowForLiveRate($allRows, $leg);
                if ($bestRow !== null) {
                    $raw = (float)($bestRow['CurrencyRate'] ?? 0);
                    $normalized = $this->normalizedOrgCurrencyRate($base, $leg, $raw);
                    if ($normalized !== null) {
                        $rates[$leg] = $normalized;
                    }
                }
            }

            $rates[$base] = 1.0;

            \Log::debug('Xero Currencies rates fetched', ['rates' => $rates, 'from' => $from, 'to' => $to, 'base' => $base]);

            if (isset($rates[$from], $rates[$to]) && $rates[$from] > 0 && $rates[$to] > 0) {
                $rate = $rates[$from] / $rates[$to];
                \Log::info('Exchange rate from Xero Currencies API', [
                    'from' => $from, 'to' => $to, 'rate' => $rate,
                ]);

                $this->liveCurrenciesRateCache[$cacheKey] = $rate;

                return $rate;
            }

            \Log::warning('Xero Currencies missing rate for requested pair', [
                'from' => $from,
                'to' => $to,
                'base' => $base,
                'has_from' => isset($rates[$from]),
                'has_to' => isset($rates[$to]),
                'rates' => $rates,
                'bulk_row_count' => count($allRows),
            ]);
        } catch (\Exception $ex) {
            \Log::warning('Xero Currencies API failed', ['from' => $from, 'to' => $to, 'message' => $ex->getMessage()]);
        }

        return null;
    }

    /**
     * Get exchange rate between two currencies using only Xero (no third-party FX APIs).
     *
     * Xero's CurrencyRate is "organisation base currency units per 1 unit of this currency"
     * (same as invoice CurrencyRate). With that, amount in {@see $to} equals amount in {@see $from}
     * multiplied by: basePerUnit(from) / basePerUnit(to).
     *
     * Portal parity (org base = AUD example): Xero's USD row "AUD per 1 USD" is the CurrencyRate for USD.
     * Then USD→AUD multiplier equals that number; AUD→USD multiplier equals its reciprocal (not the same column).
     * Compare your UI to the same direction — swapping from/to inverts the rate (product ≈ 1).
     *
     * @param string $from Source currency code (e.g. USD)
     * @param string $to Target currency code (e.g. AUD)
     * @param \DateTime|null $date Optional conversion date; invoice lookups use invoices on/before this date.
     * @return float Multiplier: amount_in_to = amount_in_from * rate (AUD↔USD rounded to match Xero Exchange rates grid)
     * @throws \Exception
     */
    public function getExchangeRate(string $from, string $to, ?\DateTime $date = null): float
    {
        $from = $this->normalizeCurrencyCode($from);
        $to = $this->normalizeCurrencyCode($to);
        $effectiveDate = $date ?? new \DateTime('now', new \DateTimeZone('UTC'));
        if ($from === $to) {
            return 1.0;
        }

        if (trim($this->tenantId) === '') {
            throw new \Exception(
                'Xero tenant ID is missing. Set XERO_TENANT_ID in .env, then run php artisan config:clear if you use config caching.'
            );
        }

        $base = $this->getOrganisationBaseCurrency();
        $isBaseLegPair = ($from === $base || $to === $base);

        // Always prefer Xero Currencies API first so returned value matches the live Xero rate table.
        $liveRate = $this->tryGetLiveCurrenciesRate($from, $to, $base);
        if ($liveRate !== null) {
            $finalRate = $this->roundRateToMatchXeroAudUsdTable($base, $from, $to, $liveRate);
            \Log::info('Exchange rate resolved', [
                'from' => $from,
                'to' => $to,
                'base' => $base,
                'rate' => $finalRate,
                'source' => 'currencies_live',
            ]);

            return $finalRate;
        }

        // Fallback to invoice CurrencyRate for base-currency legs when live Currencies is unavailable.
        // Keep this explicit in logs so we can distinguish live-vs-historical sources.
        $allowInvoiceFallback = filter_var(env('XERO_FX_ALLOW_INVOICE_FALLBACK', true), FILTER_VALIDATE_BOOLEAN);
        
        if ($isBaseLegPair && $allowInvoiceFallback) {
            \Log::warning('Live Currencies unavailable, using invoice fallback for FX', [
                'from' => $from,
                'to' => $to,
                'date' => $date?->format('Y-m-d'),
            ]);
            $rate = $this->tryGetInvoiceRate(
                $from,
                $to,
                $base,
                $effectiveDate,
                'Exchange rate from Xero invoice fallback',
                'Xero invoice rate fallback failed'
            );
            if ($rate !== null) {
                \Log::info('Exchange rate resolved', [
                    'from' => $from,
                    'to' => $to,
                    'base' => $base,
                    'rate' => $rate,
                    'source' => 'invoice_fallback',
                ]);
                return $rate;
            }
        }

        throw new \Exception(
            'Unable to fetch exchange rate from Xero for ' . $from . ' → ' . $to
            . '. Ensure currencies are enabled in Xero and/or an AUTHORISED/PAID invoice exists in the foreign currency.'
        );
    }

    /**
     * Strict live FX from Xero Currencies API only (no invoice fallback).
     *
     * @throws \Exception
     */
    public function getExchangeRateExactFromXero(string $from, string $to): float
    {
        $from = $this->normalizeCurrencyCode($from);
        $to = $this->normalizeCurrencyCode($to);
        if ($from === $to) {
            return 1.0;
        }
        if (trim($this->tenantId) === '') {
            throw new \Exception(
                'Xero tenant ID is missing. Set XERO_TENANT_ID in .env, then run php artisan config:clear if you use config caching.'
            );
        }

        $base = $this->getOrganisationBaseCurrency();
        $liveRate = $this->tryGetLiveCurrenciesRate($from, $to, $base);
        if ($liveRate === null) {
            throw new \Exception('Live exchange rate unavailable from Xero Currencies API for ' . $from . ' → ' . $to);
        }

        $exactRate = $this->roundRateToMatchXeroAudUsdTable($base, $from, $to, $liveRate);
        \Log::info('Exchange rate exact from Xero Currencies API', [
            'from' => $from,
            'to' => $to,
            'base' => $base,
            'rate' => $exactRate,
            'source' => 'currencies_live_strict',
        ]);

        return $exactRate;
    }

    /**
     * Get conversion rate using only Xero invoice data.
     * Reads CurrencyRate from the most recent invoice in the searched currency (tries AUTHORISED/PAID, then broader statuses).
     * Xero stores: CurrencyRate = how many base-currency units equal 1 unit of the invoice currency.
     * Organisation base currency is taken from Xero GET /Organisation (see {@see getOrganisationBaseCurrency}),
     * with config fallback if needed.
     *
     * Supports:
     *   - from=USD, to=AUD  => reads CurrencyRate from a USD invoice (e.g. 1.55)
     *   - from=AUD, to=USD  => reads CurrencyRate from a USD invoice, returns 1/rate
     *
     * @param string $from
     * @param string $to
     * @param \DateTime|null $date If provided, only invoices on/before this date are considered.
     * @return array ['rate' => float, 'invoice_number' => string, 'invoice_date' => string, 'source' => string]
     * @throws \Exception if no usable Xero invoice rate found
     */
    public function getXeroConversionRate(string $from, string $to, ?\DateTime $date = null): array
    {
        $from = $this->normalizeCurrencyCode($from);
        $to   = $this->normalizeCurrencyCode($to);
        $base = $this->getOrganisationBaseCurrency();

        if ($from === $to) {
            return ['rate' => 1.0, 'invoice_number' => null, 'invoice_date' => null, 'source' => 'same_currency'];
        }

        // Determine which currency to search invoices for
        // CurrencyRate on Xero invoice = 1 invoiceCurrency = X baseCurrency
        // So if from=USD,to=AUD (base=AUD): search USD invoices, rate = CurrencyRate
        // If from=AUD,to=USD (base=AUD): search USD invoices, rate = 1/CurrencyRate
        $searchCurrency = ($from !== $base) ? $from : $to;
        $isInverse      = ($from === $base);

        $accessToken = $this->getAccessToken();

        // Include DRAFT before the unfiltered pass so ACCREC invoices created but not yet
        // authorised/submitted still supply CurrencyRate (matches pre-submit workflow in Xero).
        $statusBatches = [
            'AUTHORISED,PAID',
            'SUBMITTED,AUTHORISED,PAID',
            'DRAFT',
            null,
        ];

        foreach ($statusBatches as $statuses) {
            $invoices = $this->fetchInvoicesForCurrency($accessToken, $searchCurrency, $statuses, $date);
            foreach ($invoices as $inv) {
                $currencyRateRaw = (float)($inv['CurrencyRate'] ?? 0);
                $invCurrency = strtoupper((string) ($inv['CurrencyCode'] ?? ''));
                if ($currencyRateRaw <= 0) {
                    continue;
                }
                if ($invCurrency !== '' && $invCurrency !== $base && abs($currencyRateRaw - 1.0) < 0.000001) {
                    continue;
                }

                $currencyRate = $this->xeroCurrencyRateAsBasePerForeignUnit($base, $searchCurrency, $currencyRateRaw);
                if ($currencyRate <= 0) {
                    continue;
                }

                $rate = $isInverse
                    ? (1 / $currencyRate)
                    : $currencyRate;
                $rate = $this->roundRateToMatchXeroAudUsdTable($base, $from, $to, $rate);

                \Log::info('Xero conversion rate from invoice', [
                    'from'              => $from,
                    'to'                => $to,
                    'rate'              => $rate,
                    'invoice_number'    => $inv['InvoiceNumber'] ?? null,
                    'invoice_date'      => $inv['DateString'] ?? null,
                    'invoice_status'    => $inv['Status'] ?? null,
                    'statuses_filter'   => $statuses,
                    'currency_rate_raw' => $currencyRateRaw,
                    'currency_rate'     => $currencyRate,
                    'is_inverse'        => $isInverse,
                ]);

                return [
                    'rate'           => $rate,
                    'invoice_number' => $inv['InvoiceNumber'] ?? null,
                    'invoice_date'   => $inv['DateString'] ?? null,
                    'currency_rate'  => $currencyRate,
                    'source'         => 'xero_invoice',
                ];
            }
        }

        throw new \Exception(
            'No Xero invoice found with CurrencyRate for ' . $searchCurrency . '. '
            . 'Add at least one invoice in ' . $searchCurrency . ' (any status), or enable the currency with a rate under Settings → Currencies.'
        );
    }

    /**
     * Upsert (create or update) a contact in Xero, with supplier/customer flags
     */
    public function upsertContact(array $data, $isSupplier = false, $isCustomer = true): array
    {
        $api = $this->getAccountingApi();
        $where = 'Name=="' . addslashes($data['Name']) . '"';
        \Log::debug('Xero upsertContact flags', [
            'name' => $data['Name'],
            'isSupplier' => $isSupplier,
            'isCustomer' => $isCustomer
        ]);
        $existingContacts = $api->getContacts($this->tenantId, null, $where)->getContacts();
        // Always send at least one address (real or placeholder)
        $addresses = [];
        if (!empty($data['Addresses'])) {
            foreach ($data['Addresses'] as $addressData) {
                $address = new Address();
                $address->setAddressType($addressData['AddressType'] ?? 'STREET');
                $address->setAddressLine1($addressData['AddressLine1'] ?? '');
                $address->setCity($addressData['City'] ?? '');
                $address->setRegion($addressData['Region'] ?? '');
                $address->setPostalCode($addressData['PostalCode'] ?? '');
                $address->setCountry($addressData['Country'] ?? '');
                $addresses[] = $address;
            }
        } else {
            // Placeholder address if none provided
            $address = new Address();
            $address->setAddressType('STREET');
            $address->setAddressLine1('Default Address Line');
            $address->setCity('Default City');
            $address->setRegion('Default Region');
            $address->setPostalCode('0000');
            $address->setCountry('Default Country');
            $addresses[] = $address;
        }

        // Log the address payload for debugging
        \Log::debug('Xero contact address payload', [
            'addresses' => array_map(function($a) {
                return [
                    'AddressType' => $a->getAddressType(),
                    'AddressLine1' => $a->getAddressLine1(),
                    'City' => $a->getCity(),
                    'Region' => $a->getRegion(),
                    'PostalCode' => $a->getPostalCode(),
                    'Country' => $a->getCountry(),
                ];
            }, $addresses)
        ]);

        if (!empty($existingContacts)) {
            // Update the first found contact
            $contact = $existingContacts[0];
            $contact->setFirstName($data['FirstName'] ?? $contact->getFirstName());
            $contact->setLastName($data['LastName'] ?? $contact->getLastName());
            $contact->setEmailAddress($data['EmailAddress'] ?? $contact->getEmailAddress());
            $contact->setIsSupplier($isSupplier);
            $contact->setIsCustomer($isCustomer);

            if (!empty($data['Phones'])) {
                $phones = [];
                foreach ($data['Phones'] as $phoneData) {
                    $phone = new Phone();
                    $phone->setPhoneType($phoneData['PhoneType'] ?? 'DEFAULT');
                    $phone->setPhoneNumber($phoneData['PhoneNumber'] ?? '');
                    $phones[] = $phone;
                }
                $contact->setPhones($phones);
            }

            $contact->setAddresses($addresses);

            // Add ContactPersons (buyer details) if provided
            if (!empty($data['ContactPersons'])) {
                $contactPersons = [];
                foreach ($data['ContactPersons'] as $personData) {
                    $person = new \XeroAPI\XeroPHP\Models\Accounting\ContactPerson();
                    $person->setFirstName($personData['FirstName'] ?? '');
                    $person->setLastName($personData['LastName'] ?? '');
                    $person->setEmailAddress($personData['EmailAddress'] ?? '');
                    $contactPersons[] = $person;
                }
                $contact->setContactPersons($contactPersons);
            }

            $updateResponse = $api->updateContact($this->tenantId, $contact->getContactId(), $contact);
            $updatedContacts = $updateResponse->getContacts();
            $updatedContact = $updatedContacts[0];
            return [
                'contact_id' => $updatedContact->getContactId(),
                'name' => $updatedContact->getName(),
                'email_address' => $updatedContact->getEmailAddress(),
            ];
        } else {
            // Create new contact and return contact_id
            $contact = new Contact();
            $contact->setName($data['Name']);
            $contact->setFirstName($data['FirstName'] ?? null);
            $contact->setLastName($data['LastName'] ?? null);
            $contact->setEmailAddress($data['EmailAddress'] ?? null);
            $contact->setIsSupplier($isSupplier);
            $contact->setIsCustomer($isCustomer);

            if (!empty($data['Phones'])) {
                $phones = [];
                foreach ($data['Phones'] as $phoneData) {
                    $phone = new Phone();
                    $phone->setPhoneType($phoneData['PhoneType'] ?? 'DEFAULT');
                    $phone->setPhoneNumber($phoneData['PhoneNumber'] ?? '');
                    $phones[] = $phone;
                }
                $contact->setPhones($phones);
            }

            $contact->setAddresses($addresses);

            // Add ContactPersons (buyer details) if provided
            if (!empty($data['ContactPersons'])) {
                $contactPersons = [];
                foreach ($data['ContactPersons'] as $personData) {
                    $person = new \XeroAPI\XeroPHP\Models\Accounting\ContactPerson();
                    $person->setFirstName($personData['FirstName'] ?? '');
                    $person->setLastName($personData['LastName'] ?? '');
                    $person->setEmailAddress($personData['EmailAddress'] ?? '');
                    $contactPersons[] = $person;
                }
                $contact->setContactPersons($contactPersons);
            }

            $contactsWrapper = new Contacts();
            $contactsWrapper->setContacts([$contact]);
            $response = $api->createContacts($this->tenantId, $contactsWrapper);
            $contacts = $response->getContacts();
            if (empty($contacts)) {
                throw new Exception('No contact returned from Xero.');
            }
            $contact = $contacts[0];
            return [
                'contact_id' => $contact->getContactId(),
                'name' => $contact->getName(),
                'email_address' => $contact->getEmailAddress(),
            ];
        }
    }

    /**
     * Create a supplier contact in Xero (uses upsertContact)
     */
    public function createSupplier(array $data): array
    {
        // Correct: Supplier should have isSupplier=true, isCustomer=false
        return $this->upsertContact($data, true, false);
    }

    /**
     * Create a customer contact in Xero (uses upsertContact)
     */
    public function createCustomer(array $data): array
    {
        return $this->upsertContact($data, false, true);
    }

    /**
     * Find a supplier contact by name in Xero
     */
    public function findSupplierByName(string $name): ?array
    {
       
        // Xero does not distinguish customer/supplier in search, so filter after fetch
        $api = $this->getAccountingApi();
        $where = 'Name=="' . addslashes($name) . '"';
        $response = $api->getContacts($this->tenantId, null, $where);
        $contacts = $response->getContacts();
        if (!empty($contacts)) {
            foreach ($contacts as $contact) {
                if ($contact->getIsSupplier()) {
                    return [
                        'contact_id' => $contact->getContactId(),
                        'name' => $contact->getName(),
                        'email_address' => $contact->getEmailAddress(),
                    ];
                }
            }
        }
        return null;
    }

    /**
     * Create or update a bill (Accounts Payable) in Xero for a supplier
     * @param string $contactId
     * @param array $lineItemsData
     * @param string|null $billId
     * @return array
     */
    public function createBill(string $contactId, array $lineItemsData, string $billId = null, $invoiceDate = null, ?string $invoiceNumber = null): array
    {
        //echo $billId;die;
        $api = $this->getAccountingApi();
        $defaultExpenseAccount = '6-1200';
        $defaultTaxType = 'WOS';

        // Map internal GST types to Xero tax types (move to config/constants if desired)
        $gstToXero = [
            // Map WOS to the tenant-accepted tax type (observed as EXEMPTEXPENSES in logs)
            'WOS' => 'TAX001',
            'NONE' => 'NONE',
            'GST_FREE' => 'NONE',
            'STANDARD' => 'INPUT',
            'GST' => 'INPUT',
        ];
        $validXeroTaxTypes = ['NONE', 'INPUT', 'OUTPUT', 'WOS'];

        $lineItems = array_map(function ($item) use ($defaultExpenseAccount, $defaultTaxType, $gstToXero, $validXeroTaxTypes) {
            $lineItem = new LineItem();
            $lineItem->setDescription($item['description'] ?? 'No description');
            $lineItem->setQuantity($item['quantity'] ?? 1);
            $lineItem->setUnitAmount($item['unit_amount'] ?? 0);
            $lineItem->setAccountCode($item['account_code'] ?? $defaultExpenseAccount);

            // Determine tax type: prefer gst_type mapping, then explicit tax_type, else default
            $taxType = $defaultTaxType;
            if (!empty($item['gst_type'])) {
                $gstKey = strtoupper(trim((string)$item['gst_type']));
                if (isset($gstToXero[$gstKey])) {
                    $taxType = $gstToXero[$gstKey];
                }
            } elseif (!empty($item['tax_type'])) {
                $candidate = strtoupper(trim((string)$item['tax_type']));
                if (in_array($candidate, $validXeroTaxTypes, true)) {
                    $taxType = $candidate;
                }
            }
           
          
            $lineItem->setTaxType($taxType);
            return $lineItem;
        }, $lineItemsData);

        // CASE 1: Deal creation (no billId) - always create new bill
        if (!$billId) {        
            $invoice = new Invoice();
            $invoice->setType(Invoice::TYPE_ACCPAY);
            $invoice->setContact((new Contact())->setContactId($contactId));
            $invoice->setLineItems($lineItems);
            if ($invoiceNumber) {
                $invoice->setInvoiceNumber($invoiceNumber);
            }
            if ($invoiceDate) {
                $invoice->setDate(new \DateTime($invoiceDate));
                $dueDate = (new \DateTime($invoiceDate))->modify('+7 days');
                $invoice->setDueDate($dueDate);
            } else {
                $invoice->setDate(new \DateTime(Carbon::now()->toDateString()));
                $invoice->setDueDate(new \DateTime(Carbon::now()->addDays(7)->toDateString()));
            }
            $invoices = new Invoices();
            $invoices->setInvoices([$invoice]);
            try {
                $response = $api->createInvoices($this->tenantId, $invoices);
                $invoicesArr = $response->getInvoices();
            } catch (\Exception $ex) {
                $errorBody = method_exists($ex, 'getResponseBody') ? $ex->getResponseBody() : null;
                \Log::error('Xero bill creation exception', [
                    'exception_message' => $ex->getMessage(),
                    'contactId' => $contactId,
                    'lineItemsData' => $lineItemsData,
                    'error_body' => $errorBody,
                ]);
                throw new \Exception('Xero API exception: ' . $ex->getMessage());
            }
            if (empty($invoicesArr) || $invoicesArr[0]->getInvoiceId() === '00000000-0000-0000-0000-000000000000') {
                $errorDetails = null;
                if (method_exists($response, 'getElements')) {
                    $elements = $response->getElements();
                    if (!empty($elements)) {
                        $errorDetails = $elements;
                    }
                }
                \Log::error('Xero bill creation failed', [
                    'response' => $response,
                    'contactId' => $contactId,
                    'lineItemsData' => $lineItemsData,
                    'error_details' => $errorDetails,
                ]);
                throw new \Exception('Xero did not create a valid bill. Check API response for errors.');
            }
            $createdBill = $invoicesArr[0];
            $lineItemsArr = array_map(function($item) {
                return [
                    'description' => $item->getDescription(),
                    'quantity' => $item->getQuantity(),
                    'unit_amount' => $item->getUnitAmount(),
                    'account_code' => $item->getAccountCode(),
                    'tax_type' => method_exists($item, 'getTaxType') ? $item->getTaxType() : null,
                ];
            }, $createdBill->getLineItems() ?? []);
            return [
                'bill_id' => $createdBill->getInvoiceId(),
                'bill_number' => $createdBill->getInvoiceNumber(),
                'status' => $createdBill->getStatus(),
                'amount_due' => $createdBill->getAmountDue(),
                'total' => $createdBill->getTotal(),
                'date' => ($createdBill->getDate() ? $createdBill->getDateAsDate()->format('Y-m-d') : null),
                'due_date' => ($createdBill->getDueDate() ? $createdBill->getDueDateAsDate()->format('Y-m-d') : null),
                'line_items' => $lineItemsArr,
            ];
        }

        // CASE 2: Deal edit (billId provided) - only update or recreate if changed
        try {
            $existing = $api->getInvoice($this->tenantId, $billId);
            if ($existing instanceof \XeroAPI\XeroPHP\Models\Accounting\Invoices) {
                $invoicesArr = $existing->getInvoices();
                $existing = $invoicesArr[0] ?? null;
            }
            $canUpdate = false;
            $shouldRecreate = false;
            $existingLineItems = [];
            $existingContactId = null;
            $existingIsValid = false;
            if ($existing instanceof Invoice) {
                $existingContactId = $existing->getContact()?->getContactId();
                foreach ($existing->getLineItems() ?? [] as $item) {
                    $existingLineItems[] = [
                        'description' => trim((string)$item->getDescription()),
                        'quantity' => (float)$item->getQuantity(),
                        'unit_amount' => round((float) $item->getUnitAmount(), 4),
                        'account_code' => trim((string)$item->getAccountCode()),
                        'tax_type' => trim((string)$item->getTaxType()),
                    ];
                }
                if (!$existingContactId) {
                    \Log::warning('Xero bill missing contact', [
                        'existing_bill_id' => $billId,
                        'raw_invoice' => json_encode($existing)
                    ]);
                }
                if ($existingContactId && count($existingLineItems) > 0) {
                    $existingIsValid = true;
                }
            } else {
                \Log::warning('Xero bill not Invoice instance', [
                    'existing_bill_id' => $billId,
                    'raw_invoice' => json_encode($existing)
                ]);
            }
            if (!$existingIsValid) {
                \Log::info('Xero bill invalid for update, creating new bill', [
                    'existing_bill_id' => $billId,
                    'existing_contact_id' => $existingContactId,
                    'existing_line_items_count' => count($existingLineItems),
                ]);
                if ($existing instanceof Invoice && $existing->getStatus() !== Invoice::STATUS_VOIDED) {
                    $existing->setStatus(Invoice::STATUS_VOIDED);
                    $api->updateInvoice($this->tenantId, $billId, $existing);
                }
                $invoice = new Invoice();
                $invoice->setType(Invoice::TYPE_ACCPAY);
                $invoice->setContact((new Contact())->setContactId($contactId));
                $invoice->setLineItems($lineItems);
                if ($invoiceNumber) {
                    $invoice->setInvoiceNumber($invoiceNumber);
                }
                if ($invoiceDate) {
                    $invoice->setDate(new \DateTime($invoiceDate));
                    $dueDate = (new \DateTime($invoiceDate))->modify('+7 days');
                    $invoice->setDueDate($dueDate);
                } else {
                    $invoice->setDate(new \DateTime(Carbon::now()->toDateString()));
                    $invoice->setDueDate(new \DateTime(Carbon::now()->addDays(7)->toDateString()));
                }
                $invoices = new Invoices();
                $invoices->setInvoices([$invoice]);
                $response = $api->createInvoices($this->tenantId, $invoices);
                $invoicesArr = $response->getInvoices();
                if (empty($invoicesArr) || $invoicesArr[0]->getInvoiceId() === '00000000-0000-0000-0000-000000000000') {
                    throw new \Exception('Xero did not create a valid bill after voiding.');
                }
                $createdBill = $invoicesArr[0];
                $lineItemsArr = array_map(function($item) {
                    return [
                        'description' => $item->getDescription(),
                        'quantity' => $item->getQuantity(),
                        'unit_amount' => $item->getUnitAmount(),
                        'account_code' => $item->getAccountCode(),
                        'tax_type' => method_exists($item, 'getTaxType') ? $item->getTaxType() : null,
                    ];
                }, $createdBill->getLineItems() ?? []);
                \Log::info('Xero bill recreated (invalid original) line items', $lineItemsArr);
                return [
                    'bill_id' => $createdBill->getInvoiceId(),
                    'bill_number' => $createdBill->getInvoiceNumber(),
                    'status' => $createdBill->getStatus(),
                    'amount_due' => $createdBill->getAmountDue(),
                    'total' => $createdBill->getTotal(),
                    'date' => optional($createdBill->getDate())->format('Y-m-d'),
                    'due_date' => optional($createdBill->getDueDate())->format('Y-m-d'),
                    'line_items' => $lineItemsArr,
                ];
            }
            // Normalize and sort line items for comparison (include tax_type)
            $normalizeLineItems = function($arr) use ($gstToXero, $validXeroTaxTypes, $defaultTaxType) {
                $norm = array_map(function($li) use ($gstToXero, $validXeroTaxTypes, $defaultTaxType) {
                    if (is_array($li)) {
                        $desc = $li['description'] ?? '';
                        $qty = $li['quantity'] ?? 0;
                        $unit = $li['unit_amount'] ?? 0;
                        $acc = $li['account_code'] ?? '';
                        $tax = $defaultTaxType;
                        if (!empty($li['gst_type'])) {
                            $gstKey = strtoupper(trim((string)$li['gst_type']));
                            if (isset($gstToXero[$gstKey])) {
                                $tax = $gstToXero[$gstKey];
                            }
                        } elseif (!empty($li['tax_type'])) {
                            $cand = strtoupper(trim((string)$li['tax_type']));
                            if (in_array($cand, $validXeroTaxTypes, true)) {
                                $tax = $cand;
                            }
                        }
                    } else {
                        $desc = $li->getDescription() ?? '';
                        $qty = $li->getQuantity() ?? 0;
                        $unit = $li->getUnitAmount() ?? 0;
                        $acc = $li->getAccountCode() ?? '';
                        $tax = method_exists($li, 'getTaxType') ? trim((string)$li->getTaxType()) : $defaultTaxType;
                    }
                    return [
                        'description' => trim((string)$desc),
                        'quantity' => (float)$qty,
                        'unit_amount' => round((float) $unit, 4),
                        'account_code' => trim((string)$acc),
                        'tax_type' => $tax,
                    ];
                }, $arr);
                usort($norm, function($a, $b) {
                    return strcmp(
                        $a['description'] . $a['account_code'] . ($a['tax_type'] ?? ''),
                        $b['description'] . $b['account_code'] . ($b['tax_type'] ?? '')
                    );
                });
                return $norm;
            };
            $normExisting = $normalizeLineItems($existingLineItems);
            $normNew = $normalizeLineItems($lineItemsData);
            // Debug log for comparison
            \Log::debug('Xero bill comparison', [
                'existing_bill_id' => $billId,
                'existing_contact_id' => $existingContactId,
                'new_contact_id' => $contactId,
                'norm_existing_line_items' => $normExisting,
                'norm_new_line_items' => $normNew,
                'raw_existing_line_items' => $existingLineItems,
                'raw_new_line_items' => $lineItemsData,
            ]);
            $contactChanged = (trim((string)($existingContactId ?? '')) !== trim((string)$contactId));
            $lineItemsChanged = $normExisting !== $normNew;
            
             
            if ($existing instanceof Invoice && $existing->getStatus() === Invoice::STATUS_DRAFT) {
                $canUpdate = true;
            } else if ($contactChanged || $lineItemsChanged) {
                $shouldRecreate = true;
            }
        
          
            if ($canUpdate && ($contactChanged || $lineItemsChanged)) {
              
            // Update in place if DRAFT and changed
                $existing->setContact((new Contact())->setContactId($contactId));
                $existing->setLineItems($lineItems);
                if ($invoiceNumber) {
                    $existing->setInvoiceNumber($invoiceNumber);
                }
                if ($invoiceDate) {
                    $existing->setDate(new \DateTime($invoiceDate));
                    $dueDate = (new \DateTime($invoiceDate))->modify('+7 days');
                    $existing->setDueDate($dueDate);
                } else {
                    $existing->setDate(new \DateTime(Carbon::now()->toDateString()));
                    $existing->setDueDate(new \DateTime(Carbon::now()->addDays(7)->toDateString()));
                }
                $existing->setStatus(Invoice::STATUS_DRAFT);
                \Log::debug('Xero updateInvoice payload', [
                    'billId' => $billId,
                    'contactId' => $contactId,
                    'lineItems' => array_map(function($li) {
                        return [
                            'description' => $li->getDescription(),
                            'quantity' => $li->getQuantity(),
                            'unit_amount' => $li->getUnitAmount(),
                            'account_code' => $li->getAccountCode(),
                            'tax_type' => method_exists($li, 'getTaxType') ? $li->getTaxType() : null,
                        ];
                    }, $existing->getLineItems() ?? []),
                    'status' => $existing->getStatus(),
                ]);
                try {
                    $response = $api->updateInvoice($this->tenantId, $billId, $existing);
                } catch (\Exception $ex) {
                    $errorBody = method_exists($ex, 'getResponseBody') ? $ex->getResponseBody() : null;
                    \Log::error('Xero updateInvoice failed', [
                        'exception_message' => $ex->getMessage(),
                        'billId' => $billId,
                        'contactId' => $contactId,
                        'lineItemsData' => $lineItemsData,
                        'error_body' => $errorBody,
                    ]);
                    $shouldRetryWithNone = false;
                    $needleChecks = ['tax', 'taxType', 'TaxType', 'WOS', 'GST'];
                    if (is_string($errorBody)) {
                        foreach ($needleChecks as $n) {
                            if (stripos($errorBody, $n) !== false) {
                                $shouldRetryWithNone = true;
                                break;
                            }
                        }
                    }
                    if ($shouldRetryWithNone) {
                        foreach ($existing->getLineItems() ?? [] as $li) {
                            if (method_exists($li, 'setTaxType')) {
                                $li->setTaxType('NONE');
                            }
                        }
                        try {
                            $response = $api->updateInvoice($this->tenantId, $billId, $existing);
                            \Log::info('Xero updateInvoice succeeded on retry with NONE tax', ['billId' => $billId]);
                        } catch (\Exception $ex2) {
                            $errorBody2 = method_exists($ex2, 'getResponseBody') ? $ex2->getResponseBody() : null;
                            \Log::error('Xero updateInvoice retry failed', [
                                'exception_message' => $ex2->getMessage(),
                                'billId' => $billId,
                                'error_body' => $errorBody2,
                            ]);
                            throw $ex2;
                        }
                    } else {
                        throw $ex;
                    }
                }
                \Log::debug('Xero updateInvoice response', [
                    'response' => $response
                ]);
                $updatedBill = $response;
                if ($updatedBill instanceof \XeroAPI\XeroPHP\Models\Accounting\Invoices) {
                    $updatedBill = $updatedBill->getInvoices()[0] ?? null;
                }
                if (!$updatedBill) {
                    throw new \Exception('No invoice returned from Xero after update.');
                }
                $lineItemsArr = array_map(function($item) {
                    return [
                        'description' => $item->getDescription(),
                        'quantity' => $item->getQuantity(),
                        'unit_amount' => $item->getUnitAmount(),
                        'account_code' => $item->getAccountCode(),
                        'tax_type' => method_exists($item, 'getTaxType') ? $item->getTaxType() : null,
                    ];
                }, $updatedBill->getLineItems() ?? []);
                \Log::info('Xero bill updated line items', $lineItemsArr);
                return [
                    'bill_id' => $updatedBill->getInvoiceId(),
                    'bill_number' => $updatedBill->getInvoiceNumber(),
                    'status' => $updatedBill->getStatus(),
                    'amount_due' => $updatedBill->getAmountDue(),
                    'total' => $updatedBill->getTotal(),
                    'date' => ($updatedBill->getDate() ? $updatedBill->getDateAsDate()->format('Y-m-d') : null),
                    'due_date' => ($updatedBill->getDueDate() ? $updatedBill->getDueDateAsDate()->format('Y-m-d') : null),
                    'line_items' => $lineItemsArr,
                ];
            } else if ($shouldRecreate) {
                // Void the old bill and create a new one
                if ($existing instanceof Invoice && $existing->getStatus() !== Invoice::STATUS_VOIDED) {
                    $existing->setStatus(Invoice::STATUS_VOIDED);
                    $api->updateInvoice($this->tenantId, $billId, $existing);
                }
                // Now create a new bill
                $invoice = new Invoice();
                $invoice->setType(Invoice::TYPE_ACCPAY);
                $invoice->setContact((new Contact())->setContactId($contactId));
                $invoice->setLineItems($lineItems);
                if ($invoiceNumber) {
                    $invoice->setInvoiceNumber($invoiceNumber);
                }
                $invoice->setDate(new \DateTime(Carbon::now()->toDateString()));
                $invoice->setDueDate(new \DateTime(Carbon::now()->addDays(7)->toDateString()));
                $invoices = new Invoices();
                $invoices->setInvoices([$invoice]);
                $response = $api->createInvoices($this->tenantId, $invoices);
                $invoicesArr = $response->getInvoices();
                if (empty($invoicesArr) || $invoicesArr[0]->getInvoiceId() === '00000000-0000-0000-0000-000000000000') {
                    throw new \Exception('Xero did not create a valid bill after voiding.');
                }
                $createdBill = $invoicesArr[0];
                $lineItemsArr = array_map(function($item) {
                    return [
                        'description' => $item->getDescription(),
                        'quantity' => $item->getQuantity(),
                        'unit_amount' => $item->getUnitAmount(),
                        'account_code' => $item->getAccountCode(),
                        'tax_type' => method_exists($item, 'getTaxType') ? $item->getTaxType() : null,
                    ];
                }, $createdBill->getLineItems() ?? []);
                \Log::info('Xero bill recreated line items', $lineItemsArr);
                return [
                    'bill_id' => $createdBill->getInvoiceId(),
                    'bill_number' => $createdBill->getInvoiceNumber(),
                    'status' => $createdBill->getStatus(),
                    'amount_due' => $createdBill->getAmountDue(),
                    'total' => $createdBill->getTotal(),
                    'date' => ($createdBill->getDate() ? $createdBill->getDateAsDate()->format('Y-m-d') : null),
                    'due_date' => ($createdBill->getDueDate() ? $createdBill->getDueDateAsDate()->format('Y-m-d') : null),
                    'line_items' => $lineItemsArr,
                ];
            } else {
                // No change, return existing bill info
                $lineItemsArr = $existingLineItems;
                \Log::info('Xero bill unchanged, returning existing bill', $lineItemsArr);
                return [
                    'bill_id' => $existing->getInvoiceId(),
                    'bill_number' => $existing->getInvoiceNumber(),
                    'status' => $existing->getStatus(),
                    'amount_due' => $existing->getAmountDue(),
                    'total' => $existing->getTotal(),
                    'date' => ($existing->getDate() ? $existing->getDateAsDate()->format('Y-m-d') : null),
                    'due_date' => ($existing->getDueDate() ? $existing->getDueDateAsDate()->format('Y-m-d') : null),
                    'line_items' => $lineItemsArr,
                ];
            }
        } catch (\Exception $ex) {
            \Log::error('Xero bill update/void exception', [
                'exception_message' => $ex->getMessage(),
                'billId' => $billId,
                'contactId' => $contactId,
                'lineItemsData' => $lineItemsData,
            ]);
            throw new \Exception('Xero API exception (update/void): ' . $ex->getMessage());
        }
    }

    public function getAccessToken(): string
    {
        //$this->storeTokenFromCode('lPx2buzKAJwu8ofel4RgR2yH66vC0XZHnKgzHXDrK8w');
        
        //die;
        $token = XeroToken::first();

        if (!$token) {
            throw new \Exception("Xero token not found.");
        }

        if (Carbon::now()->lt($token->access_token_expires_at)) {
            return $token->access_token; // Still valid
        }

        // Access token expired, refresh it
        $response = Http::asForm()->post('https://identity.xero.com/connect/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token->refresh_token,
            'client_id' => env('XERO_CLIENT_ID'),
            'client_secret' => env('XERO_CLIENT_SECRET'),
        ]);

        if (!$response->ok()) {
            throw new \Exception("Failed to refresh Xero token: " . $response->body());
        }

        $data = $response->json();

        // Update DB with new token
        $token->update([
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'access_token_expires_at' => now()->addSeconds($data['expires_in']),
            'refresh_token_expires_at' => now()->addDays(60),
        ]);

        return $data['access_token'];
    }

    public function storeTokenFromCode(string $code): void
    {
        
        $response = Http::asForm()->post('https://identity.xero.com/connect/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => env('XERO_REDIRECT_URI'), // Must match what's set in your Xero app
            'client_id' => env('XERO_CLIENT_ID'),
            'client_secret' => env('XERO_CLIENT_SECRET'),
        ]);

        if (!$response->ok()) {
            throw new \Exception('Failed to get token from Xero: ' . $response->body());
        }

        $data = $response->json();
        

        // Save or update the token in the database
        XeroToken::updateOrCreate(
            ['id' => 1], // Assuming single tenant — only one record needed
            [
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'access_token_expires_at' => now()->addSeconds($data['expires_in']),
                'refresh_token_expires_at' => now()->addDays(60),
            ]
        );
    }

    // ...existing code...

    public function getTenantId()
    {
        $accessToken = $this->getAccessToken(); // Ensure you have a valid access token

        $response = Http::withToken($accessToken)
            ->get('https://api.xero.com/connections');

        if ($response->ok()) {
            $connections = $response->json();

            foreach ($connections as $connection) {
                \Log::info('Xero Tenant', [
                    'tenantId' => $connection['tenantId'],
                    'tenantName' => $connection['tenantName'],
                    'tenantType' => $connection['tenantType'],
                ]);
            }
            

            return $connections; // Return the list of tenants
        }

        throw new \Exception('Failed to fetch Xero tenants: ' . $response->body());
    }

    public function findCustomerByName(string $name): ?array
    {
        //$this->getTenantId();
        $api = $this->getAccountingApi();
        $where = 'Name=="' . addslashes($name) . '"';

        $response = $api->getContacts($this->tenantId, null, $where);
        $contacts = $response->getContacts();

        if (!empty($contacts)) {
            $contact = $contacts[0];            

            return [
                'contact_id' => $contact->getContactId(),
                'name' => $contact->getName(),
                'email_address' => $contact->getEmailAddress(),
            ];
        }

        return null;
    }

    public function createInvoice(string $contactId, array $lineItemsData): array
    {
        $api = $this->getAccountingApi();

        // Ensure line items are correctly structured
        $lineItems = array_map(function ($item) {
            $lineItem = new LineItem();
            $lineItem->setDescription($item['description'] ?? 'No description');
            $lineItem->setQuantity($item['quantity'] ?? 1);
            $lineItem->setUnitAmount($item['unit_amount'] ?? 0);
            $lineItem->setAccountCode($item['account_code'] ?? '2-1000'); // Typical Xero sales/revenue account
            if (isset($item['discount_rate'])) {
                $lineItem->setDiscountRate($item['discount_rate']);
            }
            return $lineItem;
        }, $lineItemsData);

        // Create the invoice object
        $invoice = new Invoice();
        $invoice->setType(Invoice::TYPE_ACCREC); // "ACCREC" = Accounts Receivable
        $invoice->setContact((new Contact())->setContactId($contactId));
        $invoice->setLineItems($lineItems);
        $invoice->setDate(new \DateTime(Carbon::now()->toDateString()));
        $invoice->setDueDate(new \DateTime(Carbon::now()->addDays(7)->toDateString()));
        $invoice->setStatus(Invoice::STATUS_DRAFT);

        // Wrap and send to Xero
        $invoices = new Invoices();
        $invoices->setInvoices([$invoice]);

        $response = $api->createInvoices($this->tenantId, $invoices);

        $createdInvoice = $response->getInvoices()[0];

        // Extract line items
        $lineItems = array_map(function($item) {
            return [
                'description' => $item->getDescription(),
                'quantity' => $item->getQuantity(),
                'unit_amount' => $item->getUnitAmount(),
                'account_code' => $item->getAccountCode(),
                'discount' => method_exists($item, 'getDiscountRate') ? $item->getDiscountRate() : null,
            ];
        }, $createdInvoice->getLineItems() ?? []);

        // Return invoice fields and line items
        return [
            'invoice_id' => $createdInvoice->getInvoiceId(),
            'invoice_number' => $createdInvoice->getInvoiceNumber(),
            'status' => $createdInvoice->getStatus(),
            'amount_due' => $createdInvoice->getAmountDue(),
            'total' => $createdInvoice->getTotal(),
            'date' => ($createdInvoice->getDate() ? $createdInvoice->getDateAsDate()->format('Y-m-d') : null),
            'due_date' => ($createdInvoice->getDueDate() ? $createdInvoice->getDueDateAsDate()->format('Y-m-d') : null),
            'line_items' => $lineItems,
        ];
    }

    /**
     * Get all active account codes from Xero
     * @return array [ ['code' => '1-1110', 'name' => 'Purchases', ...], ... ]
     */
    public function getActiveAccountCodes(): array
    {
        
        $api = $this->getAccountingApi();
        $accountsResponse = $api->getAccounts($this->tenantId);
        $accounts = $accountsResponse->getAccounts();
        $activeAccounts = [];
        foreach ($accounts as $account) {
            if ($account->getStatus() === 'ACTIVE') {
                $activeAccounts[] = [
                    'code' => $account->getCode(),
                    'name' => $account->getName(),
                    'type' => $account->getType(),
                    'status' => $account->getStatus(),
                ];
            }
        }
        //dd($activeAccounts); // For debugging
        return $activeAccounts;
    }

    /**
     * Fetch all TaxRates for the connected Xero tenant
     * @return array Array of Xero TaxRate model objects
     */
    public function getTaxRates(): array
    {
        $api = $this->getAccountingApi();
        try {
            $response = $api->getTaxRates($this->tenantId);
            $taxRates = $response->getTaxRates();
            return $taxRates ?? [];
        } catch (\Exception $ex) {
            \Log::error('Xero getTaxRates failed', [
                'exception_message' => $ex->getMessage(),
            ]);
            throw $ex;
        }
    }

    protected function getAccountingApi(): AccountingApi
    {
        $accessToken = $this->getAccessToken();
        //echo $accessToken;die;
        $config = Configuration::getDefaultConfiguration()->setAccessToken($accessToken);
        return new AccountingApi(new Client(), $config);
    }

    /**
     * Apply invoice date, due date (+7 days from invoice date), and optional invoice number on a Xero Invoice model.
     * When {@see $invoiceMeta} contains `invoice_date`, that date is used for issue date and due date is +7 days.
     * When `invoice_date` is absent, defaults to today and today + 7 days (previous behaviour).
     * `invoice_number` is only applied when `invoice_date` is present and the value is non-empty (and the SDK supports it).
     *
     * @param array{invoice_date?: string|null, invoice_number?: string|null} $invoiceMeta
     */
    /**
     * Buyer sale prices in WatchAdmin are stored GST-inclusive; Xero defaults to tax-exclusive line amounts.
     */
    protected function applySalesInvoiceLineAmountTypes(Invoice $invoice): void
    {
        $invoice->setLineAmountTypes(LineAmountTypes::INCLUSIVE);
    }

    protected function applyInvoiceMetaToInvoice(Invoice $invoice, array $invoiceMeta): void
    {
        $hasInvoiceDate = !empty($invoiceMeta['invoice_date']);
        if ($hasInvoiceDate) {
            $invoiceDate = Carbon::parse($invoiceMeta['invoice_date'])->startOfDay();
            $invoice->setDate(new \DateTime($invoiceDate->toDateString()));
            $invoice->setDueDate(new \DateTime($invoiceDate->copy()->addDays(7)->toDateString()));
            $num = isset($invoiceMeta['invoice_number']) ? trim((string)$invoiceMeta['invoice_number']) : '';
            if ($num !== '' && method_exists($invoice, 'setInvoiceNumber')) {
                $invoice->setInvoiceNumber($num);
            }
        } else {
            $invoice->setDate(new \DateTime(Carbon::now()->toDateString()));
            $invoice->setDueDate(new \DateTime(Carbon::now()->addDays(7)->toDateString()));
        }
    }

    /**
     * Create or update a sales invoice (Accounts Receivable) in Xero for a customer
     * @param string $contactId
     * @param array $lineItemsData
     * @param string|null $invoiceId
     * @param array{invoice_date?: string|null, invoice_number?: string|null} $invoiceMeta Optional: buyer invoice_date drives issue/due dates; invoice_number sent when date is set
     * @return array
     */
    public function createOrUpdateInvoice(string $contactId, array $lineItemsData, ?string $invoiceId = null, array $invoiceMeta = []): array
    {
        $api = $this->getAccountingApi();
        $defaultSalesAccount = '4-0510'; // Typical Xero sales/revenue account
        $defaultTaxType = 'OUTPUT';
        $gstTypePercent = config('constants.GST_TYPE_PERCENT');

        // Build a mapping of tenant TaxRate Name/TaxType -> TaxType code so front-end text
        // (e.g. "WOS" or a TaxType code) can be mapped to the Xero TaxType accepted by the tenant
        $taxRatesMap = [];
        try {
            $taxRates = $this->getTaxRates();
            foreach ($taxRates as $tr) {
                $name = strtoupper(trim((string)$tr->getName()));
                $type = strtoupper(trim((string)$tr->getTaxType()));
                if ($name !== '') {
                    $taxRatesMap[$name] = $type;
                }
                if ($type !== '') {
                    $taxRatesMap[$type] = $type;
                }
            }
        } catch (\Exception $ex) {
            \Log::warning('Failed to fetch Xero tax rates for mapping', ['message' => $ex->getMessage()]);
        }
      

        // A small list of commonly-seen Xero tax type codes/names to accept directly as fallbacks
        $validXeroTaxTypes = ['NONE', 'INPUT', 'OUTPUT', 'WOS', 'EXEMPTEXPENSES', 'TAX001'];

        $lineItems = array_map(function ($item) use ($defaultSalesAccount, $defaultTaxType, $gstTypePercent, $taxRatesMap, $validXeroTaxTypes) {
            $lineItem = new LineItem();
            $lineItem->setDescription($item['description'] ?? 'No description');
            $lineItem->setQuantity($item['quantity'] ?? 1);
            $lineItem->setUnitAmount($item['unit_amount'] ?? 0);
            $lineItem->setAccountCode($item['account_code'] ?? $defaultSalesAccount);
            // Determine tax type from incoming item: prefer front-end `gst_type`, then explicit `tax_type`, else default
            $taxType = $defaultTaxType;
            if (!empty($item['gst_type'])) {
                $rawGst = strtoupper(trim((string)$item['gst_type']));
                // Map known internal keys to the expected label/text that may match Xero TaxRate names.
                // Avoid using config constants here per request — use an inline mapping.
                $labelMap = [
                    'GST_FREE' => 'GST FREE INCOME',
                    'GST_ON_SALES' => 'GST ON INCOME',
                ];
                if (isset($labelMap[$rawGst])) {
                    $key = strtoupper($labelMap[$rawGst]);
                } else {
                    $key = $rawGst;
                }
                if (isset($taxRatesMap[$key])) {
                    $taxType = $taxRatesMap[$key];
                } elseif (in_array($key, $validXeroTaxTypes, true)) {
                    $taxType = $key;
                }
            } elseif (!empty($item['tax_type'])) {
                $cand = strtoupper(trim((string)$item['tax_type']));
                if (in_array($cand, $validXeroTaxTypes, true)) {
                    $taxType = $cand;
                }
            }
              
            $lineItem->setTaxType($taxType);
            $discount = 0;
            //if ($gstTypeKey && isset($gstTypePercent[$gstTypeKey])) {
               // $discount = $gstTypePercent[$gstTypeKey];
           // }
            $lineItem->setDiscountRate($discount);
            return $lineItem;
        }, $lineItemsData);

        // CASE 1: Deal creation (no invoiceId) - always create new invoice
        if (!$invoiceId) {
            $invoice = new Invoice();
            $invoice->setType(Invoice::TYPE_ACCREC);
            $invoice->setContact((new Contact())->setContactId($contactId));
            $invoice->setLineItems($lineItems);
            $this->applySalesInvoiceLineAmountTypes($invoice);
            $this->applyInvoiceMetaToInvoice($invoice, $invoiceMeta);
            $invoice->setStatus(Invoice::STATUS_DRAFT);
            $invoices = new Invoices();
            $invoices->setInvoices([$invoice]);
           
            try {
                $response = $api->createInvoices($this->tenantId, $invoices);
                $invoicesArr = $response->getInvoices();
            } catch (\Exception $ex) {
                $errorBody = method_exists($ex, 'getResponseBody') ? $ex->getResponseBody() : null;
                \Log::error('Xero sales invoice creation exception', [
                    'exception_message' => $ex->getMessage(),
                    'contactId' => $contactId,
                    'lineItemsData' => $lineItemsData,
                    'error_body' => $errorBody,
                ]);
                throw new \Exception('Xero API exception: ' . $ex->getMessage());
            }
            if (empty($invoicesArr) || $invoicesArr[0]->getInvoiceId() === '00000000-0000-0000-0000-000000000000') {
            //dd($invoices);    
            $errorDetails = null;
                if (method_exists($response, 'getElements')) {
                    $elements = $response->getElements();
                    if (!empty($elements)) {
                        $errorDetails = $elements;
                    }
                }
                \Log::error('Xero sales invoice creation failed', [
                    'response' => $response,
                    'contactId' => $contactId,
                    'lineItemsData' => $lineItemsData,
                    'error_details' => $errorDetails,
                ]);
                throw new \Exception('Xero did not create a valid sales invoice. Check API response for errors.');
            }
             
            $createdInvoice = $invoicesArr[0];
           
            $lineItemsArr = array_map(function($item) {
                return [
                    'description' => $item->getDescription(),
                    'quantity' => $item->getQuantity(),
                    'unit_amount' => $item->getUnitAmount(),
                    'account_code' => $item->getAccountCode(),
                    'discount' => method_exists($item, 'getDiscountRate') ? $item->getDiscountRate() : null,
                ];
            }, $createdInvoice->getLineItems() ?? []);
            return [
                'invoice_id' => $createdInvoice->getInvoiceId(),
                'invoice_number' => $createdInvoice->getInvoiceNumber(),
                'status' => $createdInvoice->getStatus(),
                'amount_due' => $createdInvoice->getAmountDue(),
                'total' => $createdInvoice->getTotal(),
                'date' => ($createdInvoice->getDate() ? $createdInvoice->getDateAsDate()->format('Y-m-d') : null),
                'due_date' => ($createdInvoice->getDueDate() ? $createdInvoice->getDueDateAsDate()->format('Y-m-d') : null),
                'line_items' => $lineItemsArr,
            ];
        }

        // CASE 2: Deal edit (invoiceId provided) - only update or recreate if changed
        try {
            $existing = $api->getInvoice($this->tenantId, $invoiceId);
            if ($existing instanceof \XeroAPI\XeroPHP\Models\Accounting\Invoices) {
                $invoicesArr = $existing->getInvoices();
                $existing = $invoicesArr[0] ?? null;
            }
            $existingLineItems = [];
            $existingContactId = null;
            $existingIsValid = false;
            $existingStatus = null;
            if ($existing instanceof Invoice) {
                $existingContactId = $existing->getContact()?->getContactId();
                $existingStatus = $existing->getStatus();
                foreach ($existing->getLineItems() ?? [] as $item) {
                    $existingLineItems[] = [
                        'description' => trim((string)$item->getDescription()),
                        'quantity' => (float)$item->getQuantity(),
                        'unit_amount' => round((float) $item->getUnitAmount(), 4),
                        'account_code' => trim((string)$item->getAccountCode()),
                        'discount' => method_exists($item, 'getDiscountRate') ? (float)$item->getDiscountRate() : null,
                    ];
                }
                if (!$existingContactId) {
                    \Log::warning('Xero sales invoice missing contact', [
                        'existing_invoice_id' => $invoiceId,
                        'raw_invoice' => json_encode($existing)
                    ]);
                }
                if ($existingContactId && count($existingLineItems) > 0) {
                    $existingIsValid = true;
                }
            } else {
                \Log::warning('Xero sales invoice not Invoice instance', [
                    'existing_invoice_id' => $invoiceId,
                    'raw_invoice' => json_encode($existing)
                ]);
            }
            if (!$existingIsValid) {
                \Log::info('Xero sales invoice invalid for update, creating new invoice', [
                    'existing_invoice_id' => $invoiceId,
                    'existing_contact_id' => $existingContactId,
                    'existing_line_items_count' => count($existingLineItems),
                ]);
                if ($existing instanceof Invoice && $existing->getStatus() !== Invoice::STATUS_VOIDED) {
                    $existing->setStatus(Invoice::STATUS_VOIDED);
                    try {
                        $api->updateInvoice($this->tenantId, $invoiceId, $existing);
                    } catch (\Exception $ex) {
                        \Log::error('Xero void invoice failed', [
                            'exception_message' => $ex->getMessage(),
                            'invoiceId' => $invoiceId,
                            'contactId' => $contactId,
                            'lineItemsData' => $lineItemsData,
                        ]);
                    }
                }
                $invoice = new Invoice();
                $invoice->setType(Invoice::TYPE_ACCREC);
                $invoice->setContact((new Contact())->setContactId($contactId));
                $invoice->setLineItems($lineItems);
                $this->applySalesInvoiceLineAmountTypes($invoice);
                $this->applyInvoiceMetaToInvoice($invoice, $invoiceMeta);
                $invoice->setStatus(Invoice::STATUS_AUTHORISED);
                $invoices = new Invoices();
                $invoices->setInvoices([$invoice]);
                $response = $api->createInvoices($this->tenantId, $invoices);
                $invoicesArr = $response->getInvoices();
                if (empty($invoicesArr) || $invoicesArr[0]->getInvoiceId() === '00000000-0000-0000-0000-000000000000') {
                    throw new \Exception('Xero did not create a valid sales invoice after voiding.');
                }
                $createdInvoice = $invoicesArr[0];
                
                $lineItemsArr = array_map(function($item) {
                    return [
                        'description' => $item->getDescription(),
                        'quantity' => $item->getQuantity(),
                        'unit_amount' => $item->getUnitAmount(),
                        'account_code' => $item->getAccountCode(),
                        'discount' => method_exists($item, 'getDiscountRate') ? $item->getDiscountRate() : null,
                    ];
                }, $createdInvoice->getLineItems() ?? []);
                \Log::info('Xero sales invoice recreated (invalid original) line items', $lineItemsArr);
                return [
                    'invoice_id' => $createdInvoice->getInvoiceId(),
                    'invoice_number' => $createdInvoice->getInvoiceNumber(),
                    'status' => $createdInvoice->getStatus(),
                    'amount_due' => $createdInvoice->getAmountDue(),
                    'total' => $createdInvoice->getTotal(),
                    'date' => ($createdInvoice->getDate() ? $createdInvoice->getDateAsDate()->format('Y-m-d') : null),
                    'due_date' => ($createdInvoice->getDueDate() ? $createdInvoice->getDueDateAsDate()->format('Y-m-d') : null),
                    'line_items' => $lineItemsArr,
                ];
            }
            $normalizeLineItems = function($arr) use ($defaultTaxType, $taxRatesMap, $validXeroTaxTypes) {
                $labelMap = [
                    'GST_FREE' => 'GST FREE INCOME',
                    'GST_ON_SALES' => 'GST ON INCOME',
                ];
                $norm = array_map(function($li) use ($defaultTaxType, $taxRatesMap, $validXeroTaxTypes, $labelMap) {
                    $desc = is_array($li) ? ($li['description'] ?? '') : ($li->getDescription() ?? '');
                    $qty = is_array($li) ? ($li['quantity'] ?? 0) : ($li->getQuantity() ?? 0);
                    $unit = is_array($li) ? ($li['unit_amount'] ?? 0) : ($li->getUnitAmount() ?? 0);
                    $acc = is_array($li) ? ($li['account_code'] ?? '') : ($li->getAccountCode() ?? '');
                    $discount = is_array($li)
                        ? (isset($li['discount']) ? (float)$li['discount'] : null)
                        : (method_exists($li, 'getDiscountRate') ? (float)$li->getDiscountRate() : null);
                    // Determine tax_type for comparison: map incoming gst_type/tax_type to tenant TaxType where possible
                    $tax = $defaultTaxType;
                    if (is_array($li)) {
                        if (!empty($li['gst_type'])) {
                            $raw = strtoupper(trim((string)$li['gst_type']));
                            if (isset($labelMap[$raw])) {
                                $key = strtoupper($labelMap[$raw]);
                            } else {
                                $key = $raw;
                            }
                            if (isset($taxRatesMap[$key])) {
                                $tax = $taxRatesMap[$key];
                            } elseif (in_array($key, $validXeroTaxTypes, true)) {
                                $tax = $key;
                            }
                        } elseif (!empty($li['tax_type'])) {
                            $cand = strtoupper(trim((string)$li['tax_type']));
                            if (in_array($cand, $validXeroTaxTypes, true)) {
                                $tax = $cand;
                            }
                        }
                    } else {
                        $tax = method_exists($li, 'getTaxType') ? trim((string)$li->getTaxType()) : $defaultTaxType;
                    }
                    return [
                        'description' => trim((string)$desc),
                        'quantity' => (float)$qty,
                        'unit_amount' => round((float) $unit, 4),
                        'account_code' => trim((string)$acc),
                        'discount' => $discount,
                        'tax_type' => $tax,
                    ];
                }, $arr);
                usort($norm, function($a, $b) {
                    return strcmp(
                        $a['description'] . $a['account_code'] . ($a['tax_type'] ?? ''),
                        $b['description'] . $b['account_code'] . ($b['tax_type'] ?? '')
                    );
                });
                return $norm;
            };
            $normExisting = $normalizeLineItems($existingLineItems);
            $normNew = $normalizeLineItems($lineItemsData);
            \Log::debug('Xero sales invoice comparison', [
                'existing_invoice_id' => $invoiceId,
                'existing_contact_id' => $existingContactId,
                'new_contact_id' => $contactId,
                'norm_existing_line_items' => $normExisting,
                'norm_new_line_items' => $normNew,
                'raw_existing_line_items' => $existingLineItems,
                'raw_new_line_items' => $lineItemsData,
            ]);
            $contactChanged = (trim((string)($existingContactId ?? '')) !== trim((string)$contactId));
            $lineItemsChanged = $normExisting !== $normNew;
            // Only update if DRAFT, otherwise always void and recreate
            if ($existing instanceof Invoice && $existingStatus === Invoice::STATUS_DRAFT && ($contactChanged || $lineItemsChanged)) {
                $existing->setContact((new Contact())->setContactId($contactId));
                $existing->setLineItems($lineItems);
                $this->applySalesInvoiceLineAmountTypes($existing);
                $this->applyInvoiceMetaToInvoice($existing, $invoiceMeta);
                $existing->setStatus(Invoice::STATUS_DRAFT);
                \Log::debug('Xero updateInvoice payload', [
                    'invoiceId' => $invoiceId,
                    'contactId' => $contactId,
                    'lineItems' => array_map(function($li) {
                        return [
                            'description' => $li->getDescription(),
                            'quantity' => $li->getQuantity(),
                            'unit_amount' => $li->getUnitAmount(),
                            'account_code' => $li->getAccountCode(),
                        ];
                    }, $existing->getLineItems() ?? []),
                    'status' => $existing->getStatus(),
                ]);
                $response = $api->updateInvoice($this->tenantId, $invoiceId, $existing);
                \Log::debug('Xero updateInvoice response', [
                    'response' => $response
                ]);
                $updatedInvoice = $response;
                
                if ($updatedInvoice instanceof \XeroAPI\XeroPHP\Models\Accounting\Invoices) {
                    $updatedInvoice = $updatedInvoice->getInvoices()[0] ?? null;
                }
                if (!$updatedInvoice) {
                    throw new \Exception('No invoice returned from Xero after update.');
                }
                $lineItemsArr = array_map(function($item) {
                    return [
                        'description' => $item->getDescription(),
                        'quantity' => $item->getQuantity(),
                        'unit_amount' => $item->getUnitAmount(),
                        'account_code' => $item->getAccountCode(),
                        'discount' => method_exists($item, 'getDiscountRate') ? $item->getDiscountRate() : null,
                    ];
                }, $updatedInvoice->getLineItems() ?? []);
                \Log::info('Xero sales invoice updated line items', $lineItemsArr);
                return [
                    'invoice_id' => $updatedInvoice->getInvoiceId(),
                    'invoice_number' => $updatedInvoice->getInvoiceNumber(),
                    'status' => $updatedInvoice->getStatus(),
                    'amount_due' => $updatedInvoice->getAmountDue(),
                    'total' => $updatedInvoice->getTotal(),
                    'date' => ($updatedInvoice->getDate() ? $updatedInvoice->getDateAsDate()->format('Y-m-d') : null),
                    'due_date' => ($updatedInvoice->getDueDate() ? $updatedInvoice->getDueDateAsDate()->format('Y-m-d') : null),
                    'line_items' => $lineItemsArr,
                ];
            } else if ($existing instanceof Invoice && $existingStatus !== Invoice::STATUS_DRAFT && ($contactChanged || $lineItemsChanged)) {
                // Void and recreate for AUTHORISED or other statuses
                $voided = false;
                $voidError = null;
                if ($existing->getStatus() !== Invoice::STATUS_VOIDED) {
                    $existing->setStatus(Invoice::STATUS_VOIDED);
                    try {
                        $api->updateInvoice($this->tenantId, $invoiceId, $existing);
                        $voided = true;
                    } catch (\Exception $ex) {
                        $voidError = $ex->getMessage();
                        \Log::error('Xero void invoice failed (recreate)', [
                            'exception_message' => $voidError,
                            'invoiceId' => $invoiceId,
                            'contactId' => $contactId,
                            'lineItemsData' => $lineItemsData,
                        ]);
                    }
                } else {
                    $voided = true;
                }
                if (!$voided) {
                    // Abort and return the Xero error
                    throw new \Exception('Xero invoice could not be voided: ' . ($voidError ?: 'Unknown error'));
                }
                $invoice = new Invoice();
                $invoice->setType(Invoice::TYPE_ACCREC);
                $invoice->setContact((new Contact())->setContactId($contactId));
                $invoice->setLineItems($lineItems);
                $this->applyInvoiceMetaToInvoice($invoice, $invoiceMeta);
                $invoice->setStatus(Invoice::STATUS_AUTHORISED);
                $invoices = new Invoices();
                $invoices->setInvoices([$invoice]);
                $response = $api->createInvoices($this->tenantId, $invoices);
                $invoicesArr = $response->getInvoices();
                if (empty($invoicesArr) || $invoicesArr[0]->getInvoiceId() === '00000000-0000-0000-0000-000000000000') {
                    throw new \Exception('Xero did not create a valid sales invoice after voiding.');
                }
                $createdInvoice = $invoicesArr[0];
                $lineItemsArr = array_map(function($item) {
                    return [
                        'description' => $item->getDescription(),
                        'quantity' => $item->getQuantity(),
                        'unit_amount' => $item->getUnitAmount(),
                        'account_code' => $item->getAccountCode(),
                        'discount' => method_exists($item, 'getDiscountRate') ? $item->getDiscountRate() : null,
                    ];
                }, $createdInvoice->getLineItems() ?? []);
                \Log::info('Xero sales invoice recreated line items (recreate)', $lineItemsArr);
                return [
                    'invoice_id' => $createdInvoice->getInvoiceId(),
                    'invoice_number' => $createdInvoice->getInvoiceNumber(),
                    'status' => $createdInvoice->getStatus(),
                    'amount_due' => $createdInvoice->getAmountDue(),
                    'total' => $createdInvoice->getTotal(),
                    'date' => ($createdInvoice->getDate() ? $createdInvoice->getDateAsDate()->format('Y-m-d') : null),
                    'due_date' => ($createdInvoice->getDueDate() ? $createdInvoice->getDueDateAsDate()->format('Y-m-d') : null),
                    'line_items' => $lineItemsArr,
                ];
            } else {
                $lineItemsArr = array_map(function($item) {
                    return [
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_amount' => $item['unit_amount'],
                        'account_code' => $item['account_code'],
                        'discount' => isset($item['discount']) ? $item['discount'] : null,
                    ];
                }, $existingLineItems);
                \Log::info('Xero sales invoice unchanged, returning existing invoice', $lineItemsArr);
                return [
                    'invoice_id' => $existing->getInvoiceId(),
                    'invoice_number' => $existing->getInvoiceNumber(),
                    'status' => $existing->getStatus(),
                    'amount_due' => $existing->getAmountDue(),
                    'total' => $existing->getTotal(),
                    'date' => ($existing->getDate() ? $existing->getDateAsDate()->format('Y-m-d') : null),
                    'due_date' => ($existing->getDueDate() ? $existing->getDueDateAsDate()->format('Y-m-d') : null),
                    'line_items' => $lineItemsArr,
                ];
            }
        } catch (\Exception $ex) {
            \Log::error('Xero sales invoice update/void exception', [
                'exception_message' => $ex->getMessage(),
                'invoiceId' => $invoiceId,
                'contactId' => $contactId,
                'lineItemsData' => $lineItemsData,
            ]);
            throw new \Exception('Xero API exception (update/void): ' . $ex->getMessage());
        }
    }

}