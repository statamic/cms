<?php

namespace Statamic\Tags;

use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Stringy\StaticStringy as Stringy;

class ParentTags extends Tags
{
    protected static $handle = 'parent';

    /**
     * The {{ parent:[field] }} tag.
     *
     * Gets a specified field value from the parent.
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        $var_name = Stringy::removeLeft($this->tag, 'parent:');

        return Arr::get($this->getParent(), $var_name)->value();
    }

    /**
     * The {{ parent }} tag.
     *
     * On its own, it simply returns the URL of the parent for a single tag,
     * or makes all the parent values available within it for a tag pair.
     *
     * @return string
     */
    public function index()
    {
        if ($this->isPair) {
            return $this->parse($this->getParent());
        }

        return $this->getParentUrl();
    }

    /**
     * Get the parent url.
     *
     * @return string
     */
    private function getParentUrl()
    {
        $parent = $this->getParent();

        return array_get($parent, 'url');
    }

    /**
     * Get the parent data.
     *
     * @return string
     */
    private function getParent()
    {
        $segments = explode('/', Str::start(Str::after(URL::getCurrent(), Site::current()->url()), '/'));
        $segment_count = count($segments);
        $segments[0] = '/';

        // Remove the current URL.
        // That's the whole point here.
        array_pop($segments);

        // Create crumbs from segments
        $segment_urls = [];
        for ($i = 1; $i <= $segment_count; $i++) {
            $segment_urls[] = URL::tidy(implode('/', $segments));
            array_pop($segments);
        }

        $segments = collect($segment_urls);

        // Find the parent by stripping away URL segments
        foreach ($segment_urls as $segment_url) {
            if ($content = Entry::findByUri($segment_url, Site::current())) {
                return $content->toAugmentedArray();
            }
        }

        return null;
    }
}
