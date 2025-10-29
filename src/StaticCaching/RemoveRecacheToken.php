<?php

namespace Statamic\StaticCaching;

class RemoveRecacheToken
{
    public function __invoke($url)
    {
        $parts = parse_url($url);

        if (! isset($parts['query'])) {
            return $url;
        }

        parse_str($parts['query'], $params);
        unset($params['__recache']);

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
