<?php

namespace Tests\Fakes\Fieldtypes;

class FieldtypeWithExtraValidationRules extends \Statamic\Extend\Fieldtype
{
    public function extraRules($data)
    {
        return [
            'test.*.one' => 'required|min:2',
            'test.*.two' => 'max:2'
        ];
    }
}
