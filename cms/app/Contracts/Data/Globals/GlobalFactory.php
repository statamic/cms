<?php

namespace Statamic\Contracts\Data\Globals;

use Statamic\Contracts\Data\Content\ContentFactory;

interface GlobalFactory extends ContentFactory
{
    /**
     * @param string $slug
     * @return $this
     */
    public function create($slug);
}
