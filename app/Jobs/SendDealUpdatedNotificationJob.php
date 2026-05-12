<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\BravoMailer;
use App\Services\InvoicePdfService;
use Illuminate\Support\Facades\View;

class SendDealUpdatedNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $deal;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($deal)
    {
        $this->deal = $deal;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Fetch all emails for deal_updated (or use deal_created if you want same recipients)
        $emails = DB::table('notification_emails')
            ->where('type', 'sales_invoice_created')
            ->where('status', true) // Only fetch active emails
            ->pluck('email')
            ->toArray();
            //dd($emails);

        // proceed even if $emails is empty so customer email still sends

        try {
            $htmlBody = view('emails.deal_updated', ['deal' => $this->deal])->render();
        } catch (\Exception $ex) {
            Log::error('Failed to render deal_updated view for Bravo email', ['error' => $ex->getMessage()]);
            $htmlBody = '';
        }

        $subject = 'Deal Updated';
        // Send internal notifications via Bravo, include shipping invoice PDF as attachment
        if (!empty($htmlBody)) {
            try {
                $service = app(InvoicePdfService::class);
                $pdf = $service->generateShippingInvoicePdf($this->deal);
                $attachments = [[
                    'name' => $pdf['filename'],
                    'content' => $pdf['content'],
                    'type' => 'application/pdf',
                ]];

                $failures = BravoMailer::sendBulk($emails, $subject, $htmlBody, $attachments);
                if (!empty($failures)) {
                    Log::warning('Some Bravo sends failed for deal_updated', ['failed_recipients' => $failures]);
                }
            } catch (\Exception $ex) {
                Log::error('Failed to generate or attach PDF for bulk Bravo sends', ['error' => $ex->getMessage()]);
                // fallback to sending without attachments
                $failures = BravoMailer::sendBulk($emails, $subject, $htmlBody);
                if (!empty($failures)) {
                    Log::warning('Some Bravo sends failed for deal_updated (no attachment)', ['failed_recipients' => $failures]);
                }
            }
        }
    }
}