<?php

namespace App\Helpers;

use Carbon\Carbon;

class Helper
{
    public static function dateIndo($date) {
        $convertDate = Carbon::parse($date)->translatedFormat('d F Y');
        return $convertDate;
    }
}

if (! function_exists('safe_rich_text')) {
    function safe_rich_text(?string $value): string
    {
        $value = strip_tags((string) $value, '<p><br><strong><b><em><i><u><ul><ol><li><a><h1><h2><h3><h4><h5><h6>');
        $value = preg_replace('/\s(?:style|srcdoc|formaction|on[a-z0-9_-]+)\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $value) ?? '';

        return preg_replace_callback('/\s(href|src)\s*=\s*(["\'])(.*?)\2/i', function (array $match): string {
            $url = trim($match[3]);

            return preg_match('/^(?:https?:\/\/|\/|#)/i', $url)
                ? $match[0]
                : ' '.$match[1].'='.$match[2].'#'.$match[2];
        }, $value) ?? '';
    }
}
