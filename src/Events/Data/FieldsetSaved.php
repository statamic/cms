<?php

namespace Statamic\Events\Data;

class FieldsetSaved extends Saved
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Fieldset saved');
    }
}
