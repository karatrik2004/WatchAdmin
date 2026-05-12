<?php

namespace App\Jobs;

use App\Helpers\BravoMailer;
use App\Services\InvoicePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendInvoiceEmailToCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $deal;

    public function __construct($deal)
    {
        $this->deal = $deal;
    }

    public function handle(): void
    {
        $this->deal->loadMissing(['dealBuyerDetail', 'watchBrandDetail', 'dealCustomerTypeDetail']);

        $salesEmails = collect([
            trim((string) optional($this->deal->dealBuyerDetail)->buyer_email),
        ])
            ->filter(fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($salesEmails)) {
            Log::warning('SendInvoiceEmailToCustomerJob: no valid buyer email found on deal', [
                'deal_id' => $this->deal->id,
                'buyer_email' => optional($this->deal->dealBuyerDetail)->buyer_email,
            ]);
            return;
        }

        $htmlBody = '';
        try {
            $htmlBody = view('emails.invoice_to_customer', ['deal' => $this->deal])->render();
        } catch (\Exception $ex) {
            Log::error('SendInvoiceEmailToCustomerJob: failed to render invoice_to_customer', [
                'deal_id' => $this->deal->id,
                'error' => $ex->getMessage(),
            ]);
        }

        $subject = 'Invoice from ' . (config('app.name') ?? 'Zman Watches');

        if ($htmlBody === '') {
            return;
        }

        $attachments = [];
        try {
            $service = app(InvoicePdfService::class);
            $pdf = $service->generateShippingInvoicePdf($this->deal);
            $attachments = [[
                'name' => $pdf['filename'],
                'content' => $pdf['content'],
                'type' => 'application/pdf',
            ]];
        } catch (\Exception $ex) {
            Log::error('SendInvoiceEmailToCustomerJob: PDF generation failed', [
                'deal_id' => $this->deal->id,
                'error' => $ex->getMessage(),
            ]);
        }

        try {
            $failures = !empty($attachments)
                ? BravoMailer::sendBulk($salesEmails, $subject, $htmlBody, $attachments)
                : BravoMailer::sendBulk($salesEmails, $subject, $htmlBody);
            if (!empty($failures)) {
                Log::warning('SendInvoiceEmailToCustomerJob: buyer send failed for some recipients', [
                    'failed_recipients' => $failures,
                ]);
            }
        } catch (\Exception $ex) {
            Log::error('SendInvoiceEmailToCustomerJob: buyer bulk send failed', ['error' => $ex->getMessage()]);
            $failures = BravoMailer::sendBulk($salesEmails, $subject, $htmlBody);
            if (!empty($failures)) {
                Log::warning('SendInvoiceEmailToCustomerJob: buyer send failed (no attachment)', [
                    'failed_recipients' => $failures,
                ]);
            }
        }
    }
}
