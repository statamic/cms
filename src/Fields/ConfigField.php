<?php

namespace Statamic\Fields;

class ConfigField extends Field
{
    public function preProcess()
    {
        $value = $this->value ?? $this->defaultValue();

        $value = $this->fieldtype()->preProcessConfig($value);

        return $this->newInstance()->setValue($value);
    }
}
