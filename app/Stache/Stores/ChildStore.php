<?php

namespace Statamic\Stache\Stores;

class ChildStore extends BasicStore
{
    protected $parent;
    protected $key;

    public function __construct($parent, $stache, $key)
    {
        parent::__construct($stache);

        $this->parent = $parent;
        $this->key = $key;
    }

    public function key()
    {
        return $this->parent->key() . '::' . $this->key;
    }

    public function getItemsFromCache($cache)
    {
        return $this->parent->getItemsFromCache($cache);
    }
}
