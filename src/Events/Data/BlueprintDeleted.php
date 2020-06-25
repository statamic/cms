<?php

namespace Statamic\Events\Data;

class BlueprintDeleted extends Deleted
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Blueprint deleted.');
    }
}
