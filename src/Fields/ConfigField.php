<?php

namespace Statamic\Fields;

class ConfigField extends Field
{
    public function preProcess()
    {
        $value = $this->value ?? $this->defaultValue();

        $this->value = $this->fieldtype()->preProcessConfig($value);

        return $this;
    }
}
