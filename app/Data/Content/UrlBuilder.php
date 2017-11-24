<?php

namespace Statamic\Data\Content;

use Statamic\API\Str;
use Statamic\API\URL;
use Statamic\Contracts\Data\Content\UrlBuilder as UrlBuilderContract;

class UrlBuilder implements UrlBuilderContract
{
    /**
     * @var \Statamic\Contracts\Data\Entry|\Statamic\Data\Taxonomy
     */
    protected $content;

    /**
     * @param \Statamic\Contracts\Data\Entry|\Statamic\Data\Taxonomies\Term $content
     * @return $this
     * @throws \Exception
     */
    public function content($content)
    {
        if (! in_array($content->contentType(), ['entry', 'term'])) {
            throw new \Exception('Invalid content type. Must be entry or taxonomy.');
        }

        $this->content = $content;

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

        preg_match_all('/{\s*([a-zA-Z0-9_\-]+)\s*}/', $route_url, $matches);

        $url = $route_url;

        foreach ($matches[1] as $key => $variable) {
            // Get the corresponding value for the provided variable.
            $value = $this->getValue($variable);

            // If the value is an array, we'll grab the first value. This is useful
            // for including a taxonomy term (an array) as part of the URI.
            if (is_array($value)) {
                $value = reset($value);
            }

            // Slugify it because we're dealing with URLs after all.
            $value = $this->slugify($value);

            // Replace the variable in the URL.
            $url = str_replace($matches[0][$key], $value, $url);
        }

        // If provided variables had no matching value, we would end up with
        // blank spaces in the URL, possibly resulting in double slashes.
        // Tidying up the URL will de-duplicate those extra slashes.
        $url = URL::tidy($url);

        return Str::ensureLeft($url, '/');
    }

    private function slugify($value)
    {
        $placeholder = strtolower(str_random(16));

        $value = str_replace('_', $placeholder, $value);

        return str_replace($placeholder, '_', Str::slug($value));
    }

    /**
     * Given a route variable, get the appropriate value from the content
     *
     * @param string $variable
     * @return string
     */
    private function getValue($variable)
    {
        // Handle special values like {year}, {month}, and {day}.
        if ($specialValue = $this->getSpecialValue($variable)) {
            return $specialValue;
        }

        // Get the value from the content if it exists.
        if ($contentValue = $this->getContentValue($variable)) {
            return $contentValue;
        }

        return null;
    }

    /**
     * Get a special value based on a variable
     *
     * @param string $variable
     * @return mixed
     */
    private function getSpecialValue($variable)
    {
        switch ($variable) {
            case 'year':
                $value = $this->content->date()->format('Y');
                break;
            case 'month':
                $value = $this->content->date()->format('m');
                break;
            case 'day':
                $value = $this->content->date()->format('d');
                break;
            default:
                $value = null;
        }

        return $value;
    }

    /**
     * Get a value from the content based on a variable
     *
     * @param string $variable
     * @return mixed
     */
    private function getContentValue($variable)
    {
        // If the given variable exists as data on the content object
        // (ie. in the front-matter), we'll just use that as-is.
        if ($this->content->has($variable)) {
            return $this->content->get($variable);
        }

        // Otherwise, attempt to get it from a method on the object if one exists.
        // This will allow us to reference dynamic values like ->title() and so on.
        $method = Str::camel($variable);

        return (method_exists($this->content, $method)) ? $this->content->$method() : null;
    }
}
