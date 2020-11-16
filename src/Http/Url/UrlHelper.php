<?php

namespace Statamic\Http\Url;

class UrlHelper
{
    /**
     * Unescapes query params array brackets for increased readability.
     * 
     * @param $value
     * @return string
     */
    public static function unescapeQueryParamArrayBrackets($value) {
        return preg_replace_callback('/%5[BD](?=[^&]*=)/i', function($match) {
            return urldecode($match[0]); 
        }, $value);
    }

    /**
     * Builds a query string using with http_build_query, while also unescaping query params array brackets.
     * 
     * @param $queryAssociativeArray
     * @return string
     */
    public static function buildQueryString($queryAssociativeArray) {
        $value = http_build_query($queryAssociativeArray);

        return self::unescapeQueryParamArrayBrackets($value);
    }

    /**
     * Gets the anchor value and preprends it with a "#" if a value is retrieved.
     * 
     * @param $queryAssociativeArray
     * @return string
     */
    public static function getAnchor($value) {
        $fragment = parse_url($value, PHP_URL_FRAGMENT);

        return is_null($fragment) ? '' : "#{$fragment}";
    }

    /**
     * Parses a URL, updates its query params using a callback and rebuilds it properly.
     * 
     * @param $value
     * @param $params
     * @param $callback
     * @return string
     */
    public static function parseUrlAndRebuildQueryParams($value, $params, $callback) {
        if (isset($params[0])) {
            // Remove query params (and any following anchor) from the URL.
            $url = strtok($value, '?');
            $url = strtok($url, '#');

            // Get the anchor value and preprend it with a "#" if a value is retrieved.
            $anchor = self::getAnchor($value);

            // $queryAssociativeArray = $callback($value, $params, $url, $anchor);
            $value = $callback($value, $params, $url, $anchor);
        }

        return $value;
    }
}
