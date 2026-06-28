<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class BravoMailer
{
    /**
     * Send HTML email to a single recipient via Bravo API using curl.
     * Returns true on success, false otherwise.
     */
    public static function sendHtml(string $to, string $subject, string $html, array $attachments = [], array $cc = []): bool
    {
        $apiUrl = config('services.bravo.url', env('BRAVO_API_URL'));
        $apiKey = config('services.bravo.key', env('BRAVO_API_KEY'));
          
          
        if (empty($apiUrl) || empty($apiKey)) {
            Log::warning('Bravo API credentials not configured; cannot send email', ['to' => $to]);
            return false;
        }

        $payload = [
            'sender' => [
                'name' => env('MAIL_FROM_NAME', config('app.name')),
                'email' => env('MAIL_FROM_ADDRESS', ''),
            ],
            'to' => [ [ 'email' => $to ] ],
            // optional cc
            'cc' => [],
            'subject' => $subject,
            'htmlContent' => $html,
        ];

        // Attachments: Bravo API expects attachments base64-encoded in an 'attachment' array (if supported)
        if (!empty($attachments)) {
            $payload['attachment'] = [];
            foreach ($attachments as $att) {
                $content = $att['content'] ?? '';
                // If content is binary, ensure base64 encoding
                $b64 = base64_encode($content);
                $payload['attachment'][] = [
                    'name' => $att['name'] ?? 'attachment',
                    'content' => $b64,
                    'type' => $att['type'] ?? 'application/octet-stream',
                ];
            }
        }
        // Populate CC if provided
        if (!empty($cc)) {
            $payload['cc'] = [];
            foreach ($cc as $c) {
                $payload['cc'][] = ['email' => $c];
            }
        } else {
            unset($payload['cc']);
        }
        

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'api-key: ' . $apiKey,
            'content-type: application/json',
        ]);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $curlErr = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        //dd($response);

        if ($curlErr || $httpCode >= 400) {
            Log::error('Bravo email send failed', [
                'to' => $to,
                'error' => $curlErr,
                'http_code' => $httpCode,
                'response' => $response,
            ]);
            return false;
        }

        Log::info('Bravo email sent', ['to' => $to, 'response' => $response]);
        return true;
    }

    /**
     * Send the same HTML email to multiple recipients. Returns array of failures (empty if none).
     */
    public static function sendBulk(array $recipients, string $subject, string $html, array $attachments = [], array $cc = []): array
    {
        $failures = [];
        foreach ($recipients as $to) {
            $ok = self::sendHtml($to, $subject, $html, $attachments, $cc);
            if (! $ok) {
                $failures[] = $to;
            }
        }
        return $failures;
    }
}