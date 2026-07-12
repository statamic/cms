<?php

namespace Tests;

use Closure;
use Statamic\Fields\FieldsetLoader;
use Tests\Factories\FieldsetFactory;

class FakeFieldsetLoader
{
    protected $fieldsets;

    public function load($fieldset)
    {
        return $this->fieldsets[$fieldset];
    }

    public function with($name, $fieldset)
    {
        if ($fieldset instanceof Closure) {
            $fieldset = $fieldset(new FieldsetFactory)->create();
        }

        $this->fieldsets[$name] = $fieldset;

        return $this;
    }

    public function bind()
    {
        app()->instance(FieldsetLoader::class, $this);

        return $this;
    }
}
