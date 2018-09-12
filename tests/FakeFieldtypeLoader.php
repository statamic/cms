<?php

namespace Tests;

use Statamic\Extend\Management\FieldtypeLoader;

class FakeFieldtypeLoader extends FieldtypeLoader
{
    protected $fieldtypes;

    public function load($type, $config)
    {
        return new $this->fieldtypes[$type];
    }

    public function with($type, $class)
    {
        $this->fieldtypes[$type] = $class;

        return $this;
    }

    public function bind()
    {
        app()->instance(FieldtypeLoader::class, $this);

        return $this;
    }
}
