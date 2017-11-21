<?php

namespace Statamic\Contracts\Data\Pages;

interface PageTreeReorderer
{
    /**
     * @param array $tree
     * @return mixed
     */
    public function reorder(array $tree);
}
