<?php

if (! function_exists('page_title')) {
    function page_title(string $title, bool $withAppName = true): string
    {
        if ($withAppName) {
            $appName = config('app.name');
            $title = "$appName | $title";
        }

        return $title;
    }
}
