<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\XeroService;
use App\Models\XeroToken;
use Illuminate\Support\Facades\Log;

class RefreshXeroToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xero:refresh-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Xero access token using refresh token (runs from scheduler)';

    protected XeroService $xero;

    public function __construct(XeroService $xero)
    {
        parent::__construct();
        $this->xero = $xero;
    }

    public function handle()
    {
        try {
            // getAccessToken() will refresh the token in DB if expired
            $accessToken = $this->xero->getAccessToken();

            $tokenRecord = XeroToken::first();
            $expiresAt = $tokenRecord->access_token_expires_at ?? null;

            Log::info('Xero token refresh executed by scheduler', [
                'access_token_present' => (bool)$accessToken,
                'access_token_expires_at' => (string)$expiresAt,
            ]);

            $this->info('Xero token refreshed. Expires at: ' . ($expiresAt ? $expiresAt : 'unknown'));
            return 0;
        } catch (\Exception $e) {
            Log::error('Xero token refresh failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->error('Xero token refresh failed: ' . $e->getMessage());
            return 1;
        }
    }
}
