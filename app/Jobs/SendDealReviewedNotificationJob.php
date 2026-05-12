<?php

namespace App\Jobs;

use App\Helpers\BravoMailer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendDealReviewedNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $deal;

    public function __construct($deal)
    {
        $this->deal = $deal;
    }

    public function handle()
    {
        $emails = DB::table('notification_emails')
            ->where('type', 'deal_reviewed_ready_for_funding')
            ->where('status', true)
            ->pluck('email')
            ->toArray();

        if (empty($emails)) {
            return;
        }

        try {
            $htmlBody = view('emails.deal_reviewed', ['deal' => $this->deal])->render();
        } catch (\Exception $ex) {
            Log::error('Failed to render deal_reviewed view for Bravo email', ['error' => $ex->getMessage()]);
            return;
        }

        $subject = 'Deal Reviewed - Ready for Funding';
        $failures = BravoMailer::sendBulk($emails, $subject, $htmlBody);

        if (!empty($failures)) {
            Log::warning('Some Bravo sends failed for deal_reviewed', ['failed_recipients' => $failures]);
        }
    }
}
