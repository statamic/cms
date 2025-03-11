<?php

namespace Statamic\Fields;

class FieldtypeRepository
{
    protected $madeSelectableInForms = [];
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

    public function makeSelectableInForms($handle)
    {
        $this->madeSelectableInForms[] = $handle;
    }

    public function makeUnselectableInForms($handle)
    {
        if ($this->hasBeenMadeSelectableInForms($handle)) {
            $this->madeSelectableInForms = array_diff($this->madeSelectableInForms, [$handle]);
        }
    }

    public function hasBeenMadeSelectableInForms($handle)
    {
        return in_array($handle, $this->madeSelectableInForms);
    }
}
