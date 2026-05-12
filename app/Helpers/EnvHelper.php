<?php
if (!function_exists('getAppName')) {
    /**
     * Get the app name from the environment or fallback to a default value.
     *
     * @return string
     */
    function getAppName()
    {
        return env('APP_NAME', 'Laravel');
    }
}

if (!function_exists('getEnvValue')) {
    /**
     * Get the value of any environment variable.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function getEnvValue($key, $default = null)
    {
        return env($key, $default);
    }
}

if (!function_exists('isProduction')) {
    /**
     * Check if the application is in production mode.
     *
     * @return bool
     */
    function isProduction()
    {
        return env('APP_ENV') === 'production';
    }
}

if (!function_exists('isLocal')) {
    /**
     * Check if the application is running in a local environment.
     *
     * @return bool
     */
    function isLocal()
    {
        return env('APP_ENV') === 'local';
    }
}

if (!function_exists('getAppUrl')) {
    /**
     * Get the app URL from the environment configuration.
     *
     * @return string
     */
    function getAppUrl()
    {
        return env('APP_URL', 'http://localhost');
    }
}
