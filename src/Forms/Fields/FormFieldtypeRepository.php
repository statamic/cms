<?php

namespace Statamic\Forms\Fields;

use Illuminate\Support\Collection;
use Statamic\Exceptions\FormFieldtypeNotFoundException;

class FormFieldtypeRepository
{
    private $formFieldtypes;

    public function find(string $handle)
    {
        if (isset($this->formFieldtypes[$handle])) {
            return clone $this->formFieldtypes[$handle];
        }

        if (! ($formFields = $this->classes())->has($handle)) {
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
}
