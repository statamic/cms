<?php

namespace Statamic\Search\Searchables;

use Statamic\Search\ProvidesSearchables;

abstract class Provider implements ProvidesSearchables
{
    protected $keys;

    public function setKeys(array $keys): static
    {
        $this->keys = $keys;

        return $this;
    }

    protected function usesWildcard()
    {
        return in_array('*', $this->keys);
    }
}
