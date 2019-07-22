<?php

namespace Statamic\Tags\Taxonomy;

use Statamic\Tags\Tags;
use Statamic\Tags\OutputsItems;

class Taxonomy extends Tags
{
    use OutputsItems;

    protected $defaultAsKey = 'terms';

    /**
     * {{ taxonomy:* }} ... {{ /taxonomy:* }}
     */
    public function wildcard($tag)
    {
        $this->parameters['from'] = $tag;

        return $this->index();
    }

    /**
     * {{ taxonomy from="" }} ... {{ /taxonomy }}
     */
    public function index()
    {
        $terms = $this->terms()->get();

        return $this->output($terms);
    }

    protected function terms()
    {
        return new Terms($this->parameters);
    }
}
