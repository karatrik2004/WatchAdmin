<?php

if (!function_exists('slugify')) {
    /**
     * Convert a string to a URL-friendly slug (e.g., "My New Post!" -> "my-new-post").
     *
     * @param string $string
     * @return string
     */
    function slugify($string) {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
    }
}

if (!function_exists('titleCase')) {
    /**
     * Convert a string to title case (e.g., "hello world" -> "Hello World").
     *
     * @param string $string
     * @return string
     */
    function titleCase($string) {
        return ucwords(strtolower($string));
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
    function formatCurrency($number, $currencySymbol = '$') {
        return $currencySymbol . number_format($number, 2);
    }
}

if (!function_exists('flattenArray')) {
    /**
     * Flatten a multi-dimensional array into a single-level array.
     *
     * @param array $array
     * @return array
     */
    function flattenArray(array $array) {
        $flattened = [];
        array_walk_recursive($array, function($value) use (&$flattened) {
            $flattened[] = $value;
        });
        return $flattened;
    }
}

if (!function_exists('isValidEmail')) {
    /**
     * Check if a given string is a valid email.
     *
     * @param string $email
     * @return bool
     */
    function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('arrayExcept')) {
    /**
     * Return an array except for the specified keys.
     *
     * @param array $array
     * @param array $keys
     * @return array
     */
    function arrayExcept(array $array, array $keys) {
        return array_diff_key($array, array_flip($keys));
    }
}

if (!function_exists('generateRandomString')) {
    /**
     * Generate a random string of specified length.
     *
     * @param int $length
     * @return string
     */
    function generateRandomString($length = 16) {
        return bin2hex(random_bytes($length / 2));
    }
}

if (!function_exists('convertToUppercase')) {
    /**
     * Convert a string to uppercase.
     *
     * @param string $string
     * @return string
     */
    function convertToUppercase($string) {
        return strtoupper($string);
    }
}

if (!function_exists('convertToLowercase')) {
    /**
     * Convert a string to lowercase.
     *
     * @param string $string
     * @return string
     */
    function convertToLowercase($string) {
        return strtolower($string);
    }
}

if (!function_exists('getFileExtension')) {
    /**
     * Get the file extension of a given file path.
     *
     * @param string $filePath
     * @return string
     */
    function getFileExtension($filePath) {
        return pathinfo($filePath, PATHINFO_EXTENSION);
    }
}
