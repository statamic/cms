<?php

namespace Statamic\StarterKits;

class Hook
{
    public function find($path)
    {
        if (app('files')->exists($path)) {
            require_once $path;
        }

        $class = pathinfo($path, PATHINFO_FILENAME);

        if (! class_exists($class)) {
            return null;
        }

        return new $class;
    }
}
