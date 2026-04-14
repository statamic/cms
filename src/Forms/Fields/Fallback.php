<?php

namespace Statamic\Forms\Fields;

class Fallback extends FormFieldtype
{
    public function toFieldArray(): array
    {
        return $this->config();
    }
}
