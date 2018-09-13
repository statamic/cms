<?php

namespace Tests\Fakes\Fieldtypes;

class FieldtypeWithValidationRules extends \Statamic\Extend\Fieldtype
{
    public function rules()
    {
        return 'min:2|max:5';
    }
}
