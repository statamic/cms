<?php

namespace Statamic\Tags\Taxonomy;

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

        return $this->output($terms);
    }

    protected function terms()
    {
        return new Terms($this->params);
    }
}
