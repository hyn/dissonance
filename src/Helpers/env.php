<?php

if (!function_exists('env')) {
    function env(string $key, $default = null) {
        $env = getenv($key);

        return $env ?? $default;
    }
}