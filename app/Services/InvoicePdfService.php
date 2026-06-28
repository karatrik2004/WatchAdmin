<?php

namespace App\Services;

use App\Pdf\CustomPdf;
use App\Models\CompanyDetail;
use App\Models\Deal;
use App\Models\ShippingInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class InvoicePdfService
{
    /**
     * Generate shipping invoice PDF binary and filename for a deal
     *
     * @param Deal $deal
     * @return array ['content' => string, 'filename' => string]
     */
    public function generateShippingInvoicePdf(Deal $deal)
    {
        $companyDetail = $this->getCompanyDetail();
        $companyAddressLines = preg_split('/\r\n|\r|\n/', strip_tags((string) ($companyDetail->address ?? '')));
        $companyAddressLines = array_values(array_filter(array_map('trim', $companyAddressLines)));
        $invoiceNumber = optional($deal->dealBuyerDetail)->invoice_number ?? (100000 + $deal->id);
        $currency = strtoupper(optional($deal->dealBuyerDetail)->buyer_currency ?? 'USD');
        $shippingInvoice = (object) [
            'date' => date('d/m/Y'),
            'invoice_number' => $invoiceNumber,
            'shipper_name' => $companyDetail->company_name ?: 'Atarah Group Pty Ltd',
            'shipper_address_line_1' => $companyAddressLines[0] ?? 'Collins st tower 5, level 22',
            'shipper_address_line_2' => $companyAddressLines[1] ?? '727 Collins st Docklands, Victoria, Melbourne',
            'receiver_name' => 'Time Universe watch company limited',
            'receiver_address_line_1' => '12/F CHINACHEM CAMERON CENTRE',
            'receiver_address_line_2' => '42 CAMERON ROAD TSIM SHA TSUI KOWLOON',
            'item_description' => implode("\n", array_filter([
                'Brand: '     . ($deal->watchBrandDetail->name ?? 'Unknown Brand'),
                'Model: '     . ($deal->model_number ?? ''),        
                'Serial: '    . ($deal->serial_number ?? ''),
                'Reference: '  . ($deal->material_watch ?? ''),
                'Condition: ' . ($deal->condition ?? ''),
                'Year: '      . ($deal->year ?? ''),
                'Full Set: '  . ($deal->full_set == '1' ? 'Yes' : 'No'),
                'Dial: '  . ($deal->dial ?? ''),
            ])),
            'item_complete_set' => '',
            'item_serial' => '',
            'unit_price' => $currency . ' $' . number_format(optional($deal->dealBuyerDetail)->buyer_sale_price ?? $deal->sale_price, 2, '.', ','),
            'subtotal' => $currency . ' $' . number_format(optional($deal->dealBuyerDetail)->buyer_sale_price ?? $deal->sale_price, 2, '.', ','),
            'gst' => optional($deal->dealBuyerDetail)->gst_type
                ? (Config::get('constants.GST_TYPE_PERCENT')[optional($deal->dealBuyerDetail)->gst_type] ?? optional($deal->dealBuyerDetail)->gst_type)
                : '-',
            'gst_percent_numeric' => optional($deal->dealBuyerDetail)->gst_type
                ? (Config::get('constants.GST_TYPE_PERCENT')[optional($deal->dealBuyerDetail)->gst_type] ?? 0)
                : 0,
            'gst_amount' => (function() use ($deal, $currency) {
                $price = optional($deal->dealBuyerDetail)->buyer_sale_price ?? $deal->sale_price;
                $gstType = optional($deal->dealBuyerDetail)->gst_type;
                $gstPercent = $gstType ? (Config::get('constants.GST_TYPE_PERCENT')[$gstType] ?? 0) : 0;
                return $gstPercent > 0 ? $currency . ' $' . number_format($price * $gstPercent / 100, 2, '.', ',') : $currency . ' $0.00';
            })(),
            'total_with_gst' => (function() use ($deal, $currency) {
                $price = optional($deal->dealBuyerDetail)->buyer_sale_price ?? $deal->sale_price;
                $gstType = optional($deal->dealBuyerDetail)->gst_type;
                $gstPercent = $gstType ? (Config::get('constants.GST_TYPE_PERCENT')[$gstType] ?? 0) : 0;
                return $currency . ' $' . number_format($gstPercent > 0 ? $price * (1 + $gstPercent / 100) : $price, 2, '.', ',');
            })(),
            'total_paid' => optional($deal->dealBuyerDetail)->total_paid ? $currency . ' $' . number_format($deal->dealBuyerDetail->total_paid, 2, '.', ',') : '-',
            'balance_outstanding' => (function() use ($deal, $currency) {
                $price = optional($deal->dealBuyerDetail)->buyer_sale_price ?? $deal->sale_price;
                $gstType = optional($deal->dealBuyerDetail)->gst_type;
                $gstPercent = $gstType ? (Config::get('constants.GST_TYPE_PERCENT')[$gstType] ?? 0) : 0;
                $totalWithGst = $gstPercent > 0 ? $price * (1 + $gstPercent / 100) : $price;
                $paid = optional($deal->dealBuyerDetail)->total_paid ?? 0;
                return $currency . ' $' . number_format($totalWithGst - $paid, 2, '.', ',');
            })(),
            'name' => 'Atarah Group P/L',
            'bsb' => '012606',
            'account' => '797503878',
            'bank_name' => 'ANZ',
            'bank_account_name' => 'Atarah Group Pty Ltd',
            'bank_account_number' => '945303USD00001',
            'swift_code' => 'ANZBAU3M',
            'bank_address' => '23/100 Queen St, Melbourne VIC 3000 Australia',
            'beneficiary_address' => '15 Springfield Avenue St Kilda East VIC 3183 Australia',
        ];

        $html = View::make('admin.deals.shipping-invoice-template', compact('shippingInvoice', 'companyDetail'))->render();

        $pdf = new CustomPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('Zman Watches');
        $pdf->SetAuthor('Zman Watches');
        $pdf->SetTitle('Sales Invoice Template');
        $pdf->SetSubject('Sales Invoice');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

        $fileName = 'sales_invoice_' . ($invoiceNumber) . '.pdf';
        $content = $pdf->Output($fileName, 'S');

        return ['content' => $content, 'filename' => $fileName];
    }

    /**
     * Generate sales invoice PDF binary and filename for a deal
     *
     * @param Deal $deal
     * @return array ['content' => string, 'filename' => string]
     */
    public function generateSalesInvoicePdf(Deal $deal)
    {
        $companyDetail = $this->getCompanyDetail();
        $deal->loadMissing(['dealBuyerDetail', 'watchBrandDetail']);

        $currency = strtolower(optional($deal->dealBuyerDetail)->buyer_currency ?? 'usd');
        $buyerDetail = optional($deal->dealBuyerDetail);
        $companyDetailModel = optional($deal->dealCustomerTypeDetail);

        $customerName = $buyerDetail->buyer_name
            ?? ($deal->customer_type === 'individual'
                ? trim(($deal->first_name ?? '') . ' ' . ($deal->last_name ?? ''))
                : ($companyDetailModel->company_name ?? ''));

        $customerAddress = $buyerDetail->buyer_address
            ?? ($deal->customer_type === 'individual'
                ? $deal->address
                : ($companyDetailModel->company_address ?? ''));

        $customerZipcode = $buyerDetail->buyer_zipcode
            ?? ($deal->customer_type === 'individual'
                ? $deal->zipcode
                : ($companyDetailModel->company_zip_code ?? ''));

        $customerCity = $buyerDetail->buyer_city
            ?? ($deal->customer_type === 'individual'
                ? $deal->city
                : ($companyDetailModel->company_city ?? ''));

        $customerState = $buyerDetail->buyer_state
            ?? ($deal->customer_type === 'individual'
                ? $deal->state
                : ($companyDetailModel->company_state ?? ''));

        $basePrice = (float)($buyerDetail->buyer_sale_price ?? $deal->sale_price ?? 0);
        $gstPercent = 0;
        if (!empty($buyerDetail->gst_type)) {
            $gstMap = Config::get('constants.GST_TYPE_PERCENT', []);
            $gstType = $buyerDetail->gst_type;
            $gstPercent = isset($gstMap[$gstType]) ? (float)$gstMap[$gstType] : (float)$gstType;
        }

        $total = $basePrice;
        if ($gstPercent > 0) {
            $gstAmount = round($total * $gstPercent / (100 + $gstPercent), 2);
            $subtotalValue = round($total - $gstAmount, 2);
        } else {
            $gstAmount = 0;
            $subtotalValue = round($total, 2);
        }

        $invoiceDate = $buyerDetail->invoice_date
            ? Carbon::parse($buyerDetail->invoice_date)->format('d/m/Y')
            : date('d/m/Y');

       // $invoiceNo = $deal->invoice_no ?? (100000 + $deal->id);
// Use the Xero invoice number if available, otherwise fall back to computed number
        $invoiceNo = ($buyerDetail && !empty($buyerDetail->invoice_number))
            ? $buyerDetail->invoice_number
            : (100000 + $$deal->id);

        $deal->date = $invoiceDate;
        $deal->invoice_no = $invoiceNo;
        $deal->customer_name = $customerName;
        $deal->customer_address = $customerAddress;
        $deal->customer_zipcode = $customerZipcode;
        $deal->customer_city = $customerCity;
        $deal->customer_state = $customerState;
        $deal->subtotal = number_format($subtotalValue, 2, '.', ',');
        $deal->gst_amount = number_format($gstAmount, 2, '.', ',');
        $deal->total = number_format($total, 2, '.', ',');
        $deal->amount_due = number_format($total, 2, '.', ',');
        $deal->item_details = (object) [
            'brand' => $deal->watchBrandDetail->name ?? 'N/A',
            'model' => $deal->model_number,
            'reference' => $deal->material_watch ?? 'N/A',
            'serial' => $deal->serial_number,
            'year' => $deal->year,
            'condition' => $deal->condition,
            'complete_set' => $deal->full_set == '1' ? 'Yes' : 'No',
            'dial' => $deal->dial ?? 'N/A',
        ];

        $view = $currency === 'aud' ? 'admin.deals.invoice-aud' : 'admin.deals.invoice-usd';
        $html = View::make($view, compact('deal', 'companyDetail'))->render();

        $pdf = new CustomPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->invoiceNo = $deal->invoice_no;
        $pdf->amountDue = $deal->amount_due;
        $pdf->currency = $currency;
        $pdf->SetCreator('Zman Watches');
        $pdf->SetAuthor('Zman Watches');
        $pdf->SetTitle($deal->customer_name . ' Invoice #' . $invoiceNo);
        $pdf->SetSubject('Invoice');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetHeaderMargin(15);
        $pdf->SetFooterMargin(15);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');

        $fileSuffix = strtoupper($currency);
        $fileName = 'sales_invoice_' . $invoiceNo . '_' . $fileSuffix . '.pdf';
        $content = $pdf->Output($fileName, 'S');

        return ['content' => $content, 'filename' => $fileName];
    }

    /**
     * Generate a single shipping invoice PDF containing multiple deals in one table
     *
     * @param \Illuminate\Support\Collection $deals
     * @return array ['content' => string, 'filename' => string]
     */
    public function generateBulkShippingInvoicePdf($deals)
    {
        $companyDetail = $this->getCompanyDetail();
        $items = [];

        foreach ($deals as $deal) {
            $currency = strtoupper(optional($deal->dealBuyerDetail)->buyer_currency ?? 'USD');
            $price = (float)(optional($deal->dealBuyerDetail)->buyer_sale_price ?? $deal->sale_price);
            $gstType = optional($deal->dealBuyerDetail)->gst_type;
            $gstPercent = $gstType ? (Config::get('constants.GST_TYPE_PERCENT')[$gstType] ?? 0) : 0;
            $gstAmount = $gstPercent > 0 ? $price * $gstPercent / 100 : 0;
            $finalAmount = $price + $gstAmount;

            $invoiceNumber = optional($deal->dealBuyerDetail)->invoice_number;

            $items[] = [
                'description' => implode("\n", array_filter([
                    'Brand: '     . ($deal->watchBrandDetail->name ?? 'Unknown Brand'),
                    'Model: '     . ($deal->model_number ?? ''),
                    'Serial: '    . ($deal->serial_number ?? ''),
                    'Reference : '  . ($deal->material_watch ?? ''),
                    'Condition: ' . ($deal->condition ?? ''),
                    'Year: '      . ($deal->year ?? ''),
                    'Full Set: '  . ($deal->full_set == '1' ? 'Yes' : 'No'),
                    'Dial : '  . ($deal->dial ?? '')
                ])),
                'unit_price' => $currency . ' $' . number_format($price, 2, '.', ','),
                'gst' => $gstPercent > 0 ? $gstPercent . '%' : '-',
                'amount' => $currency . ' $' . number_format($finalAmount, 2, '.', ','),
            ];
        }

        $html = View::make('admin.deals.bulk-shipping-invoice-template', compact('items', 'companyDetail'))->render();

        $pdf = new CustomPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('Zman Watches');
        $pdf->SetAuthor('Zman Watches');
        $pdf->SetTitle('Bulk Shipping Invoice');
        $pdf->SetSubject('Bulk Shipping Invoice');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

        $fileName = 'bulk_shipping_invoice_' . date('Ymd_His') . '.pdf';
        $content = $pdf->Output($fileName, 'S');

        return ['content' => $content, 'filename' => $fileName];
    }

    /**
     * Generate PDF for a saved ShippingInvoice (portal invoice)
     *
     * @param \App\Models\ShippingInvoice $invoice
     * @return array ['content' => string, 'filename' => string]
     */
    public function generateSavedShippingInvoicePdf(\App\Models\ShippingInvoice $invoice)
    {
        $invoice->loadMissing(['items.deal.dealBuyerDetail']);
        $companyDetail = $this->getCompanyDetail();

        $items = $invoice->items->map(function ($item) {
            $gstPercent = $item->gst_type
                ? (Config::get('constants.GST_TYPE_PERCENT')[$item->gst_type] ?? 0)
                : 0;
            $u   = (float) $item->unit_price;
            $ccy = $item->lineCurrencyCode();
            $fmt = $ccy . ' $' . number_format($u, 2, '.', ',');

            return [
                'description' => $item->description,
                'unit_price'  => $fmt,
                'gst'         => $gstPercent > 0 ? $gstPercent . '%' : '-',
                'amount'      => $fmt,
            ];
        })->all();

        $longItemDescription = $invoice->items->contains(function ($item) {
            return substr_count((string) $item->description, "\n") >= 4;
        });
        $pagebreakBeforePayment = $longItemDescription || $invoice->items->count() > 3;

        $currencyCodes = $invoice->items
            ->map(fn($item) => $item->lineCurrencyCode())
            ->unique()
            ->values();

        $showTotalsInPdf = $currencyCodes->count() === 1;
        $totalsCurrencyCode = $showTotalsInPdf ? (string) $currencyCodes->first() : null;
        $subtotalAmount = $showTotalsInPdf ? (float) $invoice->items->sum('unit_price') : null;
        $totalAmount = $showTotalsInPdf ? (float) $invoice->items->sum('amount') : null;

        $html = View::make('admin.shipping-invoices.pdf-template', [
            'invoice'                 => $invoice,
            'items'                   => $items,
            'pagebreakBeforePayment'  => $pagebreakBeforePayment,
            'companyDetail'           => $companyDetail,
            'showTotalsInPdf'         => $showTotalsInPdf,
            'subtotalLabel'           => $showTotalsInPdf ? ($totalsCurrencyCode . ' $' . number_format($subtotalAmount, 2, '.', ',')) : null,
            'totalLabel'              => $showTotalsInPdf ? ($totalsCurrencyCode . ' $' . number_format($totalAmount, 2, '.', ',')) : null,
        ])->render();

        $pdf = new CustomPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('Zman Watches');
        $pdf->SetAuthor('Zman Watches');
        $pdf->SetTitle('Shipping Invoice');
        $pdf->SetSubject('Shipping Invoice');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

        $fileName = 'shipping_invoice_' . $invoice->invoice_number . '.pdf';
        $content  = $pdf->Output($fileName, 'S');

        return ['content' => $content, 'filename' => $fileName];
    }

    /**
     * Build the saved shipping-invoice PDF and store under public/shipping-invoices.
     * pdf_path column stores a path relative to the public/ directory, e.g. shipping-invoices/SI-2026-0001.pdf
     */
    public function storeSavedShippingInvoicePdf(ShippingInvoice $invoice): void
    {
        $invoice->loadMissing(['items.deal.watchBrandDetail', 'items.deal.dealBuyerDetail']);
        $result   = $this->generateSavedShippingInvoicePdf($invoice);
        $safe     = preg_replace('/[^A-Za-z0-9._-]+/', '-', (string) $invoice->invoice_number);
        $safe     = $safe !== '' ? $safe : ('invoice-' . $invoice->id);
        $relative = 'shipping-invoices/' . $safe . '.pdf';
        $full     = public_path($relative);
        $dir      = dirname($full);
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        File::put($full, $result['content']);
        $invoice->update(['pdf_path' => $relative]);
    }

    private function getCompanyDetail(): CompanyDetail
    {
        return CompanyDetail::query()->latest('id')->first() ?? new CompanyDetail();
    }
}