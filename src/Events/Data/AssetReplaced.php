<?php

namespace Statamic\Events\Data;

class AssetReplaced extends Saved
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __(':item replaced.', ['item' => 'Asset']);
    }
}
