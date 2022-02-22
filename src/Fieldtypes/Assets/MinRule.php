<?php

namespace Statamic\Fieldtypes\Assets;

class MinRule extends SizeBasedRule
{
    /**
     * Determine if the the rule passes for the given size.
     *
     * @param  int  $size
     * @return bool
     */
    public function sizePasses($size)
    {
        return $size >= $this->parameters[0];
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return str_replace(':min', $this->parameters[0], __('statamic::validation.min.file'));
    }
}
