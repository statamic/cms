<?php

namespace Statamic\Events\Data;

class CollectionDeleted extends Deleted
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Collection deleted.');
    }
}
