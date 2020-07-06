<?php

namespace Statamic\Events\Data;

class AssetSaved extends Saved
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Asset saved');
    }
}
