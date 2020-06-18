<?php

namespace Statamic\Events\Data;

class AssetMoved extends Saved
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __(':item moved.', ['item' => 'Asset']);
    }
}
