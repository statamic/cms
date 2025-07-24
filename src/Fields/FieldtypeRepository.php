<?php

namespace Statamic\Fields;

class FieldtypeRepository
{
    protected $selectableInForms = [];
    private $fieldtypes = [];

    public function preloadable()
    {
        return $this->classes()->filter(function ($class) {
            return $class::preloadable();
        });
    }

    public function find($handle)
    {
        if (isset($this->fieldtypes[$handle])) {
            return (clone $this->fieldtypes[$handle])->removeField();
        }

        if (! ($fieldtypes = $this->classes())->has($handle)) {
            throw new \Statamic\Exceptions\FieldtypeNotFoundException($handle);
        }

        return $this->fieldtypes[$handle] = app($fieldtypes->get($handle));
    }

    public function classes()
    {
        return app('statamic.fieldtypes');
    }

    public function handles()
    {
        return $this->classes()->map(function ($class) {
            return $class::handle();
        });
    }

    public function all()
    {
        return $this->handles()->map(fn ($handle) => $this->find($handle));
    }

    public function makeSelectableInForms($handle)
    {
        $this->selectableInForms[$handle] = true;
    }

    public function makeUnselectableInForms($handle)
    {
        $this->selectableInForms[$handle] = false;
    }

    public function hasBeenMadeSelectableInForms($handle)
    {
        return $this->selectableInForms[$handle] ?? false;
    }

    public function selectableInFormIsOverriden($handle)
    {
        return array_key_exists($handle, $this->selectableInForms);
    }
}
