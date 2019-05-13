<?php

namespace Statamic\Stache\Stores;

class ChildStore extends BasicStore
{
    protected $parent;
    protected $childKey;

    public function setParent(AggregateStore $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    public function parent()
    {
        return $this->parent;
    }

    public function setChildKey($key)
    {
        $this->childKey = $key;

        return $this;
    }

    public function childKey()
    {
        return $this->childKey;
    }

    public function key()
    {
        return $this->parent->key() . '::' . $this->childKey;
    }

    public function getItemsFromCache($cache)
    {
        return $this->parent->getItemsFromCache($cache);
    }

    public function cache()
    {
        parent::cache();

        $this->parent->cacheMetaKeys();
    }

    public function uncache()
    {
        parent::uncache();

        $this->parent->cacheMetaKeys();
    }
}
