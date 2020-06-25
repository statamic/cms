<?php

namespace Statamic\Events\Data;

class FieldsetDeleted extends Deleted
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Fieldset deleted.');
    }
}
