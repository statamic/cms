<?php

namespace Statamic\Validation;

class CodeFieldtypeRulers
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        foreach ($value as $key => $val) {
            if (! is_int($key) || ! in_array($val, ['dashed', 'solid'])) {
                return false;
            }
        }

        return true;
    }
}
