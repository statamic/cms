<?php

namespace Statamic\Routing;

use Statamic\Contracts\Routing\UrlBuilder as UrlBuilderContract;
use Statamic\Facades\Antlers;
use Statamic\Facades\URL;
use Statamic\Support\Str;

class UrlBuilder implements UrlBuilderContract
{
    /**
     * @var \Statamic\Contracts\Entries\Entry|\Statamic\Taxonomies\Term
     */
    protected $content;

    protected $merged = [];

    /**
     * @param  \Statamic\Contracts\Entries\Entry|\Statamic\Taxonomies\Term  $content
     * @return $this
     *
     * @throws \Exception
     */
    public function content($content)
    {
        $this->content = $content;

        return $this;
    }

    public function merge(array $merged)
    {
        $this->merged = $merged;

        return $this;
    }

    /**
     * @param $route
     * @return string
     *
     * @throws \Statamic\Exceptions\InvalidEntryTypeException
     */
    public function build($route)
    {
        // Routes can be defined as a string for just the route URL,
        // or they can be an array with a route for each locale.
        $route = (is_array($route)) ? $route[$this->content->locale()] : $route;

        $route = $this->convertToAntlers($route);

        $url = Antlers::parse($route, $this->routeData());

        // Slugify it because we're dealing with URLs after all.
        $url = $this->slugify($url);

        // If provided variables had no matching value, we would end up with
        // blank spaces in the URL, possibly resulting in double slashes.
        // Tidying up the URL will de-duplicate those extra slashes.
        $url = URL::tidy($url);

        $url = rtrim($url, '/');

        return Str::ensureLeft($url, '/');
    }

    private function convertToAntlers($route)
    {
        if (Str::contains($route, '{{')) {
            return $route;
        }

        return preg_replace_callback('/{\s*([a-zA-Z0-9_\-]+)\s*}/', function ($match) {
            return "{{ {$match[1]} }}";
        }, $route);
    }

    private function routeData()
    {
        return array_merge($this->content->routeData(), $this->merged);
    }

    private function slugify($value)
    {
        $slashPlaceholder = strtolower(str_random());
        $dotPlaceholder = strtolower(str_random());

        $value = str_replace('/', $slashPlaceholder, $value);
        $value = str_replace('.', $dotPlaceholder, $value);

        $value = Str::slug($value);

        $value = str_replace($slashPlaceholder, '/', $value);
        $value = str_replace($dotPlaceholder, '.', $value);

        return $value;
    }
}
