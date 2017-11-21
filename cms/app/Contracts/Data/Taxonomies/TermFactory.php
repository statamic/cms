<?php

namespace Statamic\Contracts\Data\Taxonomies;

use Statamic\Contracts\Data\Content\ContentFactory;

interface TermFactory extends ContentFactory
{
    /**
     * @param string $slug
     * @return $this
     */
    public function create($slug);

    /**
     * @param string $taxonomy
     * @return $this
     */
    public function taxonomy($taxonomy);
}
