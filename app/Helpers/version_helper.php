<?php

if (! function_exists('app_version')) {
    function app_version(bool $withPrefix = false): string
    {
        $version = config('Cakra')->version;

        return $withPrefix ? 'v' . $version : $version;
    }
}

if (! function_exists('app_version_label')) {
    function app_version_label(): string
    {
        $cakra = config('Cakra');

        return 'v' . $cakra->version . ' (' . $cakra->releaseDate . ')';
    }
}
