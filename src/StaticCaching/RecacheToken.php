<?php

namespace Statamic\StaticCaching;

use Statamic\Facades\StaticCache;

class RecacheToken
{
    public static function removeFromUrl($url)
    {
        $parts = parse_url($url);

        if (! isset($parts['query'])) {
            return $url;
        }

        parse_str($parts['query'], $params);
        unset($params[StaticCache::recacheTokenParameter()]);

        $query = http_build_query($params);

        $result = $parts['scheme'].'://'.$parts['host'];

        if (isset($parts['path'])) {
            $result .= $parts['path'];
        }

        if ($query) {
            $result .= '?'.$query;
        }

        return $result;
    }
}
