<?php

namespace Statamic\Stache\Stores;

class ChildStore extends BasicStore
{
    protected $parent;
    protected $key;

    public function setParent(AggregateStore $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    public function setKey($key)
    {
        $this->key = $key;

        return $this;
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
