<?php

namespace Statamic\Events\Data;

class AssetContainerDeleted extends Deleted
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Asset container deleted.');
    }
}
