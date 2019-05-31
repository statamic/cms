<?php

namespace Statamic\Data\Content;

use Statamic\API\Str;
use Statamic\API\URL;
use Statamic\API\Antlers;
use Statamic\Contracts\Data\Content\UrlBuilder as UrlBuilderContract;

class UrlBuilder implements UrlBuilderContract
{
    /**
     * @var \Statamic\Contracts\Data\Entry|\Statamic\Data\Taxonomy
     */
    protected $content;

    protected $merged = [];

    /**
     * @param \Statamic\Contracts\Data\Entry|\Statamic\Data\Taxonomies\Term $content
     * @return $this
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
     * @throws \Statamic\Exceptions\InvalidEntryTypeException
     */
    public function build($route)
    {
        // Routes can be defined as a string for just the route URL,
        // or they can be an array with a route for each locale.
        $route_url = (is_array($route)) ? $route[$this->content->locale()] : $route;

        $url = Antlers::parse($route_url, $this->routeData());

        // Slugify it because we're dealing with URLs after all.
        $url = $this->slugify($url);

        // If provided variables had no matching value, we would end up with
        // blank spaces in the URL, possibly resulting in double slashes.
        // Tidying up the URL will de-duplicate those extra slashes.
        $url = URL::tidy($url);

        $url = rtrim($url, '/');

        return Str::ensureLeft($url, '/');
    }

    private function routeData()
    {
        return array_merge($this->content->routeData(), $this->merged);
    }

    private function slugify($value)
    {
        $underscorePlaceholder = strtolower(str_random(16));
        $slashPlaceholder = strtolower(str_random(16));

        $value = str_replace('_', $underscorePlaceholder, $value);
        $value = str_replace('/', $slashPlaceholder, $value);

        $value = Str::slug($value);

        $value = str_replace($underscorePlaceholder, '_', $value);
        $value = str_replace($slashPlaceholder, '/', $value);

        return $value;
    }
}
