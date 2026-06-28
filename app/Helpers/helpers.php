<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

if (!function_exists('isActiveUrl')) {
    function isActiveUrl($patterns, $output = 'active')
    {
        foreach ((array)$patterns as $pattern) {
            if (Request::is($pattern)) {
                return $output;
            }
        }
        return '';
    }
}

if (!function_exists('sendEmail')) {
    /**
     * Secure and robust email sending helper function.
     *
     * @param string|array $to Recipient email address(es)
     * @param string $subject Email subject
     * @param string $view Blade template view name
     * @param array $data Data to pass to the email view
     * @param string|null $from Sender's email address (optional)
     * @param array $attachments File paths to attach (optional)
     * @param array $cc CC recipients (optional)
     * @return bool|string Returns true on success or error message on failure.
     */
    function sendEmail($to, $subject, $view, $data = [], $from = null, $attachments = [], $cc = [])
    {
        try {
            /*$result = sendEmail(
                ["user1@example.com", "user2@example.com"],
                "Project Update",
                "emails.sample",
                ['name' => 'Team', 'messageContent' => 'See attached document.'],
                "admin@yourapp.com",
                [storage_path('app/public/report.pdf')],
                ["manager@example.com", "supervisor@example.com"]
            );*/
            // Validate recipient emails
            $to = filterEmails($to);
            $cc = filterEmails($cc);

            if (empty($to)) {
                throw new Exception('Invalid recipient email address.');
            }

            Mail::send($view, $data, function ($message) use ($to, $subject, $from, $attachments, $cc) {
                $message->to($to)
                    ->subject($subject);

                if ($from) {
                    $message->from($from);
                }

                if (!empty($cc)) {
                    $message->cc($cc);
                }

                // Securely attach files
                foreach ($attachments as $file) {
                    if (file_exists($file)) {
                        $message->attach($file);
                    } else {
                        Log::warning("Attachment not found: " . $file);
                    }
                }
            });

            if (count(Mail::failures()) > 0) {
                throw new Exception('Mail sending failed.');
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Email sending error: ' . $e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * Validate and filter email addresses.
     *
     * @param string|array $emails Email(s) to validate
     * @return array Valid email addresses
     */
    function filterEmails($emails)
    {
        if (is_string($emails)) {
            $emails = [$emails];
        }

        return array_filter($emails, function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        });
    }
}

if (!function_exists('slugify')) {
    /**
     * Convert a string to a URL-friendly slug (e.g., "My New Post!" -> "my-new-post").
     *
     * @param string $string
     * @return string
     */
    function slugify($string)
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
    }
}

if (!function_exists('formatCurrency')) {
    /**
     * Format a number as a currency string (e.g., 1000 -> "$1,000.00").
     *
     * @param float|int $number
     * @param string $currencySymbol
     * @return string
     */
    function formatCurrency($number, $currencySymbol = '$')
    {
        return $currencySymbol . number_format($number, 2);
    }
}

if (!function_exists('getFileExtension')) {
    /**
     * Get the file extension of a given file path.
     *
     * @param string $filePath
     * @return string
     */
    function getFileExtension($filePath)
    {
        return pathinfo($filePath, PATHINFO_EXTENSION);
    }
}

if (!function_exists('formatDate')) {
    /**
     * Format date to `Y-m-d` (e.g., 2025-03-20).
     *
     * @param string|Carbon|null $date
     * @return string
     */
    function formatDate($date = null)
    {
        $date = $date ?: Carbon::now();
        return Carbon::parse($date)->format('Y-m-d');  // Example: 2025-03-20
    }
}

if (!function_exists('formatDateTime')) {
    /**
     * Format date and time to `Y-m-d H:i:s` (e.g., 2025-03-20 14:30:00).
     *
     * @param string|Carbon|null $dateTime
     * @return string
     */
    function formatDateTime($dateTime = null)
    {
        $dateTime = $dateTime ?: Carbon::now();
        return Carbon::parse($dateTime)->format('Y-m-d H:i:s');  // Example: 2025-03-20 14:30:00
    }
}

if (!function_exists('formatTime')) {
    /**
     * Format time to `h:i A` (12-hour format) (e.g., 02:30 PM).
     *
     * @param string|Carbon|null $dateTime
     * @return string
     */
    function formatTime($dateTime = null)
    {
        $dateTime = $dateTime ?: Carbon::now();
        return Carbon::parse($dateTime)->format('h:i A');  // Example: 02:30 PM
    }
}

if (!function_exists('isValidEmail')) {
    /**
     * Validate if the given email is valid.
     *
     * @param string $email
     * @return bool
     */
    function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('generateRandomString')) {
    /**
     * Generate a random alphanumeric string of the specified length.
     *
     * @param int $length
     * @return string
     */
    function generateRandomString($length = 16)
    {
        return bin2hex(random_bytes($length / 2));
    }
}

if (!function_exists('getArrayValue')) {
    /**
     * Get a value from an array with a default fallback.
     *
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function getArrayValue(array $array, $key, $default = null)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }
}

if (!function_exists('generateUniqueFileName')) {
    /**
     * Generate a unique file name based on current timestamp and original file extension.
     *
     * @param string $filePath
     * @return string
     */
    function generateUniqueFileName($filePath)
    {
        $extension = getFileExtension($filePath);
        return time() . '.' . $extension;
    }
}

if (!function_exists('redirectBackWithMessage')) {
    /**
     * Redirect back with a custom success or error message.
     *
     * @param string $message
     * @param string $status
     * @return \Illuminate\Http\RedirectResponse
     */
    function redirectBackWithMessage($message, $status = 'success')
    {
        return redirect()->back()->with($status, $message);
    }
}

if (!function_exists('truncateString')) {
    /**
     * Truncate a string to a specific length and add ellipsis.
     *
     * @param string $string
     * @param int $length
     * @return string
     */
    function truncateString($string, $length = 50)
    {
        return strlen($string) > $length ? substr($string, 0, $length) . '...' : $string;
    }
}

if (!function_exists('send_email_in_background_using_curl')) {

    /**
     * Send email in the background using curl (without blocking the main process).
     * This method will use curl to trigger an API endpoint or PHP script asynchronously.
     *
     * @param string $scriptPath The path to the PHP script or URL endpoint for email.
     * @param array $parameters The parameters to pass to the PHP script (e.g., permitId, email).
     * @return void
     */
    function send_email_in_background_using_curl($scriptPath, $parameters = [])
    {
        // Use curl to make an asynchronous request to the URL or PHP script
        $ch = curl_init();

        // Setup curl options
        curl_setopt($ch, CURLOPT_URL, $scriptPath);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // We do not need the response
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0); // No timeout
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);  // Optional, for debugging
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        // Execute curl asynchronously in the background
        curl_exec($ch);
        curl_close($ch);
    }
}




