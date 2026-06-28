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

class SendDealCreatedNotificationJob implements ShouldQueue
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
        // Fetch all emails for deal_created
        $emails = DB::table('notification_emails')
            ->where('type', 'deal_created')
            ->where('status', true) // Only fetch active emails
            ->pluck('email')
            ->toArray();
           

        if (empty($emails)) {
            return;
        }

        // Render the same email view used previously
        try {
            $htmlBody = view('emails.deal_created', ['deal' => $this->deal])->render();
        } catch (\Exception $ex) {
            Log::error('Failed to render deal_created view for Bravo email', ['error' => $ex->getMessage()]);
            return;
        }
        

        $subject = 'New Deal Created';

        // Use helper to send bulk
        $failures = BravoMailer::sendBulk($emails, $subject, $htmlBody);
        if (!empty($failures)) {
            Log::warning('Some Bravo sends failed', ['failed_recipients' => $failures]);
        }
    }
}
