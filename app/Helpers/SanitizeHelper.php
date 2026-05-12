<?php

namespace App\Helpers;

class SanitizeHelper
{
    /**
     * General sanitization function for dynamic inputs
     */
    public static function sanitize($key, $value)
    {
        if (is_array($value)) {
            return array_map([self::class, 'sanitize'], array_keys($value), $value);
        }

        // Detect content type based on the key name and apply relevant sanitization
        if (self::isHtmlKey($key)) {
            return self::sanitizeHtml($value);
        }
        if (self::isEmailKey($key)) {
            return self::sanitizeEmail($value);
        }
        if (self::isFilenameKey($key)) {
            return self::sanitizeFilename($value);
        }
        if (self::isContentKey($key)) {
            return self::sanitizeContent($value);
        }
        if (self::isUrlKey($key)) {
            return self::sanitizeUrl($value);
        }

        // Default sanitization for general text input
        return self::sanitizeText($value);
    }

    /**
     * Detect if the key holds rich HTML content that must preserve tags
     */
    private static function isHtmlKey($key)
    {
        return preg_match('/bank_details/i', $key);
    }

    /**
     * Sanitize HTML content: strip scripts and event handlers but keep formatting tags
     */
    private static function sanitizeHtml($value)
    {
        // Remove JS event handlers
        $value = preg_replace('/on[a-z]+\s*=\s*["\'][^"\']*["\']/i', '', $value);
        // Strip <script> and <style> tags
        $value = preg_replace('/<script[^>]*?>.*?<\/script>/is', '', $value);
        $value = preg_replace('/<style[^>]*?>.*?<\/style>/is', '', $value);
        return $value;
    }

    /**
     * Detect if the key is related to an email field
     */
    private static function isEmailKey($key)
    {
        return preg_match('/email/i', $key); // Matches "email", "user_email", "contact_email", etc.
    }

    /**
     * Detect if the key is related to a filename field
     */
    private static function isFilenameKey($key)
    {
        return preg_match('/file|filename|attachment|document/i', $key); // Matches "filename", "user_file", etc.
    }

    /**
     * Detect if the key is related to content (rich text, descriptions)
     */
    private static function isContentKey($key)
    {
        return preg_match('/content|description|message|text|body/i', $key);
    }

    /**
     * Detect if the key is related to a URL field
     */
    private static function isUrlKey($key)
    {
        return preg_match('/url|link|website/i', $key); // Matches "url", "website", "user_website", etc.
    }

    /**
     * Sanitize email input
     */
    private static function sanitizeEmail($email)
    {
        $email = trim($email);

        // Allow only alphanumeric characters, dots, and common symbols
        if (preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/', $email)) {
            return filter_var($email, FILTER_SANITIZE_EMAIL);
        }

        return null;
    }


    /**
     * Sanitize file names to prevent directory traversal attacks
     */
    private static function sanitizeFilename($filename)
    {
        // Prevent directory traversal using .. in the filename
        $filename = preg_replace('/\.\.(\/|\\|%2e%2e)/i', '', $filename);

        // Strip out unwanted characters and only allow alphanumeric, dot, hyphen, and underscore
        return preg_replace('/[^\w\-.]+/', '', $filename);
    }

    /**
     * Sanitize content by removing scripts and potentially dangerous tags
     */
    private static function sanitizeContent($content)
    {
        // Remove all JavaScript event handlers
        $content = preg_replace('/on[a-z]+\s*=\s*["\'][^"\']*["\']/i', '', $content);

        // Strip <script> tags
        $content = preg_replace('/<script[^>]*?>.*?<\/script>/is', '', $content);

        // Strip any remaining HTML/PHP tags
        $content = strip_tags($content);

        // Convert special characters to HTML entities
        return htmlspecialchars($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Sanitize URL input
     */
    private static function sanitizeUrl($url)
    {
        // Remove any unwanted characters from the URL.
        $sanitizedUrl = filter_var(trim($url), FILTER_SANITIZE_URL);

        // Validate if the URL is a valid URL after sanitization
        if (filter_var($sanitizedUrl, FILTER_VALIDATE_URL)) {
            return $sanitizedUrl;
        }

        // If the URL is invalid, return null or handle it accordingly
        return null;
    }

    /**
     * Default sanitization for general text input
     */
    private static function sanitizeText($text)
    {
        return htmlspecialchars(strip_tags(trim($text)), ENT_QUOTES, 'UTF-8');
    }

}

