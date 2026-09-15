<?php

namespace Statamic\Forms\Fields;

use Facades\Statamic\Fields\FieldtypeRepository;
use Illuminate\Support\Collection;
use Statamic\Exceptions\FormFieldtypeNotFoundException;
use Statamic\Forms\Fieldtypes\Fallback;

class FormFieldtypeRepository
{
    protected $selectable = [];
    private $formFieldtypes;

    public function find(string $handle)
    {
        if (isset($this->formFieldtypes[$handle])) {
            return clone $this->formFieldtypes[$handle];
        }

        if (! ($formFields = $this->classes())->has($handle)) {
            if ($fieldtype = FieldtypeRepository::classes()->get($handle)) {
                return $this->formFieldtypes[$handle] = (new Fallback())->wrapping(app($fieldtype));
            }

            throw new FormFieldtypeNotFoundException($handle);
        }

        return $this->formFieldtypes[$handle] = app($formFields->get($handle));
    }

    public function classes(): Collection
    {
        return app('statamic.form-fieldtypes');
    }

    public function handles(): Collection
    {
        return $this->classes()->map(function ($class) {
            return $class::handle();
        });
    }

    public function makeSelectable(string $handle): void
    {
        $this->selectable[$handle] = true;
    }

    public function makeUnselectable(string $handle): void
    {
        $this->selectable[$handle] = false;
    }

    public function hasBeenMadeSelectable(string $handle): bool
    {
        return $this->selectable[$handle] ?? false;
    }

    public function selectableIsOverriden(string $handle): bool
    {
        return array_key_exists($handle, $this->selectable);
    }
}
