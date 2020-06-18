<?php

namespace Statamic\Events\Data;

class AssetDeleted extends Deleted
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __(':item deleted.', ['item' => 'Asset']);
    }
}
