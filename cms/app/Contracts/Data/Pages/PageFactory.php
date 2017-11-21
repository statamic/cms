<?php

namespace Statamic\Contracts\Data\Pages;

use Statamic\Contracts\Data\Content\ContentFactory;

interface PageFactory extends ContentFactory
{
    /**
     * @param string $url
     * @return $this
     */
    public function create($url);
}
