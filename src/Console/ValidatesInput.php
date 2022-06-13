<?php

namespace Statamic\Console;

use Illuminate\Support\Facades\Validator;

trait ValidatesInput
{
    /**
     * Check if input fails a set of validation rules, and if so print an error.
     *
     * @param  string  $input
     * @param  mixed  $rules
     * @return bool
     */
    private function validationFails($input, $rules)
    {
        $validator = Validator::make(['input' => $input], ['input' => $rules]);

        if ($validator->passes()) {
            return false;
        }

        $this->error($validator->errors()->first());

        return true;
    }
}
