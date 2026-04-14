<?php

namespace Statamic\Forms\Fields;

use Illuminate\Support\Collection;

class FormFieldRepository
{
    private $formFields;

    public function find(string $handle)
    {
        if (isset($this->formFields[$handle])) {
            return clone $this->formFields[$handle];
        }

        if (! ($formFields = $this->classes())->has($handle)) {
            throw new \Statamic\Exceptions\FieldtypeNotFoundException($handle); // todo
        }

        return $this->formFields[$handle] = app($formFields->get($handle));
    }

    public function classes(): Collection
    {
        return app('statamic.form-fields');
    }

    public function handles(): Collection
    {
        return $this->classes()->map(function ($class) {
            return $class::handle();
        });
    }
}
