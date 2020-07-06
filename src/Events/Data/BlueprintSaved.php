<?php

namespace Statamic\Events\Data;

class BlueprintSaved extends Saved
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Blueprint saved');
    }
}
