<?php

namespace Statamic\Extend\Management;

use Statamic\Tags\TagNotFoundException;

class TagLoader
{
    public function load($name, $properties)
    {
        if (! ($tags = app('statamic.tags'))->has($name)) {
            throw new TagNotFoundException("Could not find files to load the `{$name}` tag.");
        }

        return $this->init($tags->get($name), $properties);
    }

    private function init($class, $properties)
    {
        $tag = is_string($class) ? app($class) : $class;

        return tap($tag, function ($tag) use ($properties) {
            $tag->setProperties($properties);
        });
    }
}
