<?php

namespace Statamic\Events\Data;

class AssetFolderDeleted extends Deleted
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Asset folder deleted.');
    }
}
