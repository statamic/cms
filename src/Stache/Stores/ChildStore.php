<?php

namespace Statamic\Stache\Stores;

use Symfony\Component\Finder\SplFileInfo;

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
        return $this->parent->key().'::'.$this->childKey;
    }

    public function directory($directory = null)
    {
        throw_if($directory, new \LogicException('Cannot set directory on a child store.'));

        return $this->parent->childDirectory($this);
    }

    public function filter(SplFileInfo $file)
    {
        return $this->parent->filter($file);
    }

    public function makeItemFromFile($path, $contents)
    {
        return $this->parent->makeItemFromFile($path, $contents);
    }

    public function keys()
    {
        if ($this->keys) {
            return $this->keys;
        }

        return $this->keys = (new Keys($this->parent()))->load();
    }
}
