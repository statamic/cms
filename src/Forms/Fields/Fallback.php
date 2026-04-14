<?php

namespace Statamic\Forms\Fields;

class Fallback extends FormField
{
    public function toFieldArray(): array
    {
        return $this->config();
    }
}
