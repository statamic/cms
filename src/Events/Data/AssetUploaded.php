<?php

namespace Statamic\Events\Data;

class AssetUploaded extends Saved
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Asset uploaded.');
    }
}
