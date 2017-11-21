<?php

namespace Statamic\Data\Taxonomies;

use Statamic\API\Config;
use Statamic\Data\Content\ContentFactory;
use Statamic\Contracts\Data\Taxonomies\TermFactory as TermFactoryContract;

class TermFactory extends ContentFactory implements TermFactoryContract
{
    protected $slug;
    protected $taxonomy;

    /**
     * @param string $slug
     * @return $this
     */
    public function create($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @param string $taxonomy
     * @return $this
     * @deprecated
     */
    public function taxonomy($taxonomy)
    {
        $this->taxonomy = $taxonomy;

        return $this;
    }

    /**
     * @return Term
     */
    public function get()
    {
        $term = new Term;

        $term->slug($this->slug);
        $term->taxonomy($this->taxonomy);
        $term->data($this->data);

        if ($this->path) {
            $term->dataType(pathinfo($this->path)['extension']);
        } else {
            $term->dataType('yaml');
        }

        $term->syncOriginal();

        return $term;
    }
}
