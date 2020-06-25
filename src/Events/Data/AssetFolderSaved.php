<?php

namespace Statamic\Events\Data;

class AssetFolderSaved extends Saved
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Asset folder saved.');
    }
}
