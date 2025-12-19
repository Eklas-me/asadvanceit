<?php

use App\Models\SiteSetting;

if (!function_exists('getSetting')) {
    /**
     * Get a site setting value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function getSetting($key, $default = null)
    {
        return SiteSetting::get($key, $default);
    }
}

if (!function_exists('setSetting')) {
    /**
     * Set a site setting value
     *
     * @param string $key
     * @param mixed $value
     * @return \App\Models\SiteSetting
     */
    function setSetting($key, $value)
    {
        return SiteSetting::set($key, $value);
    }
}
