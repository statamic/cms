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
            return clone $this->fieldtypes[$handle];
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

    /**
     * @deprecated Use FormFieldtype::makeSelectable() instead.
     */
    public function makeSelectableInForms($handle)
    {
        $this->selectableInForms[$handle] = true;
    }

    /**
     * @deprecated Use FormFieldtype::makeUnselectable() instead.
     */
    public function makeUnselectableInForms($handle)
    {
        $this->selectableInForms[$handle] = false;
    }

    /**
     * @deprecated Use FormFieldtype::isSelectable() instead.
     */
    public function hasBeenMadeSelectableInForms($handle)
    {
        return $this->selectableInForms[$handle] ?? false;
    }

    /**
     * @deprecated Use FormFieldtype::isSelectable() instead.
     */
    public function selectableInFormIsOverriden($handle)
    {
        return array_key_exists($handle, $this->selectableInForms);
    }
}
