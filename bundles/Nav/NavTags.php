<?php

namespace Statamic\Addons\Nav;

use Statamic\API\URL;
use Statamic\API\Str;
use Statamic\API\Content;
use Statamic\Extend\Tags;

class NavTags extends Tags
{
    /**
     * @var  \Statamic\Addons\Nav\TreeFactory
     */
    private $factory;

    /**
     * The {{ nav }} tag
     *
     * @return  string
     */
    public function index()
    {
        $this->factory = new TreeFactory($this->getParams());

        $tree = $this->factory->create();

        if ($tree->isEmpty()) {
            return null;
        }

        $tree->sort($this->get('sort'));

        // Convert taxonomy fields to actual taxonomy terms.
        // This will allow taxonomy term data to be available in the template without additional tags.
        // If terms are not needed, there's a slight performance benefit in disabling this.
        if ($this->getBool('supplement_taxonomies', true)) {
            $tree->supplementTaxonomies();
        }

        return $this->parseLoop($tree->toArray());
    }

    /**
     * The {{ nav:exists }} tag
     *
     * @return  string|null
     */
    public function exists()
    {
        $this->factory = new TreeFactory($this->getParams());

        $tree = $this->factory->create();

        if ($tree->isEmpty()) {
            return null;
        }

        return $this->parse([]);
    }

    /**
     * The {{ nav:doesnt_exist }} tag
     *
     * @return  string|null
     */
    public function doesntExist()
    {
        $this->factory = new TreeFactory($this->getParams());

        $tree = $this->factory->create();

        if (! $tree->isEmpty()) {
            return null;
        }

        return $this->parse([]);
    }

    /**
     * The {{ nav:count }} tag
     *
     * @return  string
     */
    public function count()
    {
        $this->factory = new TreeFactory($this->getParams());

        $tree = $this->factory->create();

        return $tree->count();
    }

    /**
     * Get the common parameters for the tags that require tree creation
     *
     * @return  array
     */
    private function getParams()
    {
        return [
            'from'         => Str::ensureLeft($this->get('from', URL::getCurrent()), '/'),
            'depth'        => $this->getInt('max_depth', 2),
            'unpublished'  => $this->getBool('show_unpublished', false),
            'entries'      => $this->getBool('include_entries', false),
            'sort'         => $this->get('sort'),
            'include_home' => $this->getBool('include_home'),
            'exclude'      => $this->getList('exclude'),
            'conditions'   => $this->getConditionParameters(),
            'locale'       => $this->get('locale', site_locale()),
            'limit'        => $this->getInt('limit'),
            'offset'       => $this->getInt('offset'),
        ];
    }

    /**
     * Get parameters using the conditions syntax
     *
     * @return array
     */
    private function getConditionParameters()
    {
        return array_filter_key($this->parameters, function ($key) {
            return Str::contains($key, ':');
        });
    }

    /**
     * The {{ nav:breadcrumbs }} tag
     *
     * @return  string
     */
    public function breadcrumbs()
    {
        $crumbs = [];

        $url = $this->get(['url' , 'from'], URL::getCurrent());
        $locale = site_locale();

        $segments = explode('/', $url);
        $segment_count = count($segments);
        $segments[0] = '/';

        // Create crumbs from segments
        $segment_urls = [];
        for ($i = 1; $i <= $segment_count; $i++) {
            $segment_urls[] = URL::tidy(join($segments, '/'));
            array_pop($segments);
        }

        // Build up the content for each crumb
        foreach ($segment_urls as $segment_url) {
            $default_segment_uri = URL::getDefaultUri($locale, $segment_url);

            $content = Content::whereUri($default_segment_uri);

            if (! $content) {
                $content = app(\Statamic\Routing\Router::class)->getRoute($segment_url);
            }

            // Skip this segment if it results in a non-existent URI.
            // An example of when this might happen is if your entries are routed through a non-standard URL.
            // For instance, on /blog/2015/01/02/post, the parent URL /blog/2015/01/02 probably isn't an
            // actual page. The segments will get skipped until `/blog`, which probably does exist.
            if (! $content) {
                continue;
            }

            if ($content instanceof \Statamic\Contracts\Data\Content\Content) {
                $content = $content->in($locale);
            }

            $crumbs[$segment_url] = $content->toArray();
            $crumbs[$segment_url]['is_current'] = (URL::getCurrent() == $segment_url);
        }

        // Remove the homepage if requested
        if (! $this->getBool('include_home', true)) {
            array_pop($crumbs);
        }

        // Correct the order (unless they've requested it in reverse)
        if (! $this->getBool('reverse', false)) {
            $crumbs = array_reverse($crumbs);
        }

        // Parse the tag
        $output = $this->trim($this->getBool('trim', true))
                       ->parseLoop(array_values($crumbs));

        // Backspaces
        if ($backspaces = $this->getInt('backspace', 0)) {
            $output = substr($output, 0, -$backspaces);
        }

        return $output;
    }
}
