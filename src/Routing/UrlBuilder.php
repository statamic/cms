<?php

namespace Statamic\Routing;

use Statamic\Contracts\Routing\UrlBuilder as UrlBuilderContract;
use Statamic\Facades\Antlers;
use Statamic\Facades\Parse;
use Statamic\Facades\URL;
use Statamic\Support\Str;
use Statamic\View\Antlers\Language\Runtime\EnvironmentDetails;

class UrlBuilder implements UrlBuilderContract
{
    /**
     * @var \Statamic\Contracts\Entries\Entry|\Statamic\Taxonomies\Term
     */
    protected $content;

    protected $merged = [];

    /**
     * Names that mean something other than a variable to Antlers.
     */
    private const ANTLERS_KEYWORDS = [
        'true', 'false', 'null', 'void', 'list', 'as', 'and', 'or', 'xor', 'not',
        'if', 'elseif', 'else', 'unless', 'elseunless', 'endif', 'endunless',
        'switch', 'once', 'endonce', 'noparse', 'endnoparse', 'stop', 'recursive', 'new',
        'bwa', 'bwo', 'bxor', 'bnot', 'bsl', 'bsr',
        'arr', 'merge', 'pluck', 'where', 'take', 'skip', 'orderby', 'groupby',
    ];

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
     * @return string
     *
     * @throws \Statamic\Exceptions\InvalidEntryTypeException
     */
    public function build($route)
    {
        // Routes can be defined as a string for just the route URL,
        // or they can be an array with a route for each locale.
        $route = (is_array($route)) ? $route[$this->content->locale()] : $route;

        if (($url = $this->fillPlaceholders($route)) === null) {
            $route = $this->convertToAntlers($route);

            $route = Parse::config($route);

            $url = Antlers::parse($route, $this->routeData());
        }

        // Slugify it because we're dealing with URLs after all.
        $url = $this->slugify($url);

        // If provided variables had no matching value, we would end up with
        // blank spaces in the URL, possibly resulting in double slashes.
        // Tidying up the URL will de-duplicate those extra slashes.
        return URL::tidy($url, withTrailingSlash: false);
    }

    /**
     * Most routes are only placeholders, like "/blog/{slug}" or "{parent_uri}/{slug}".
     * Their values can be put straight in, without creating an Antlers parser for every URL.
     *
     * Returns null when Antlers is needed: the route is written in Antlers, a placeholder
     * isn't a plain variable name, or its value isn't a plain string or integer.
     */
    private function fillPlaceholders($route): ?string
    {
        // Antlers also compiles component tags (<s:partial />), so anything that might be one is left to it.
        if (! is_string($route) || Str::contains($route, ['{{', '<'])) {
            return null;
        }

        $data = null;
        $filled = true;

        $url = preg_replace_callback('/{\s*([a-zA-Z0-9_\-]+)\s*}/', function ($match) use (&$data, &$filled) {
            $name = $match[1];

            if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name) || in_array(strtolower($name), self::ANTLERS_KEYWORDS, true)) {
                $filled = false;

                return '';
            }

            $data ??= $this->routeData();
            $value = $data[$name] ?? null;

            if (is_int($value)) {
                return (string) $value;
            }

            // A value with braces in it would read as another placeholder or as Antlers.
            if (is_string($value) && strpbrk($value, '{}') === false) {
                return $value;
            }

            $filled = false;

            return '';
        }, $route);

        // Any braces left over aren't placeholders, so Antlers decides what they mean.
        if (! $filled || $url === null || strpbrk($url, '{}') !== false) {
            return null;
        }

        // Creating an Antlers parser also records which tags and modifiers exist at that
        // moment, and a tag registered after the view engine was created is only seen once
        // that happens again. Building a URL has always done it, so it still does.
        app(EnvironmentDetails::class);

        return $url;
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
        $slashPlaceholder = strtolower(Str::random());
        $dotPlaceholder = strtolower(Str::random());

        $value = str_replace('/', $slashPlaceholder, $value);
        $value = str_replace('.', $dotPlaceholder, $value);

        $value = Str::slug($value);

        $value = str_replace($slashPlaceholder, '/', $value);
        $value = str_replace($dotPlaceholder, '.', $value);

        return $value;
    }
}
