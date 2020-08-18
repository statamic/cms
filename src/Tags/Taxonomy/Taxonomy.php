<?php

namespace Statamic\Tags\Taxonomy;

use Statamic\Facades\Site;
use Statamic\Support\Arr;
use Statamic\Tags\Concerns;
use Statamic\Tags\Tags;

class Taxonomy extends Tags
{
    use Concerns\OutputsItems;

    protected $defaultAsKey = 'terms';

    /**
     * {{ taxonomy:* }} ... {{ /taxonomy:* }}.
     */
    public function wildcard($tag)
    {
        $this->params['from'] = $tag;

        return $this->index();
    }

    /**
     * {{ taxonomy from="" }} ... {{ /taxonomy }}.
     */
    public function index()
    {
        $terms = $this->terms()->get();

        $site = Arr::getFirst($this->params, ['site', 'locale'], Site::current()->handle());

        $terms = $terms->map->in($site);

        return $this->output($terms);
    }

    protected function terms()
    {
        return new Terms($this->params);
    }
}
