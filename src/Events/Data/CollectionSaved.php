<?php

namespace Statamic\Events\Data;

class CollectionSaved extends Saved
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Collection saved.');
    }
}
