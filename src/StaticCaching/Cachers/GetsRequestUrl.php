<?php

namespace Statamic\StaticCaching\Cachers;

use Illuminate\Http\Request;

trait GetsRequestUrl
{
    public function getUrl(Request $request)
    {
        $url = $request->getUri();

        if (config('statamic.static_caching.ignore_query_strings', false)) {
            $url = explode('?', $url)[0];
        }

        $parts = parse_url($url);

        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);

            if ($allowedQueryStrings = config('statamic.static_caching.allowed_query_strings')) {
                $query = array_intersect_key($query, array_flip($allowedQueryStrings));
            }

            if ($disallowedQueryStrings = config('statamic.static_caching.disallowed_query_strings')) {
                $disallowedQueryStrings = array_flip($disallowedQueryStrings);
                $query = array_diff_key($query, $disallowedQueryStrings);
            }

            $url = $parts['scheme'].'://'.$parts['host'].$parts['path'];

            if ($query) {
                $url .= '?'.http_build_query($query);
            }
        }

        return $url;
    }
}
