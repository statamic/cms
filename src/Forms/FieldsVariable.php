<?php

namespace Statamic\Forms;

use Statamic\Fields\ArrayableString;

class FieldsVariable extends ArrayableString
{
    public function __construct(array $fields = [])
    {
        parent::__construct(
            view('statamic::forms.fields', ['fields' => $fields])->render(),
            $fields
        );
    }

    public function toArray()
    {
        return $this->extra;
    }
}
