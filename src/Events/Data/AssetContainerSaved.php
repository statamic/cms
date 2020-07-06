<?php

namespace Statamic\Events\Data;

class AssetContainerSaved extends Saved
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Asset container saved');
    }
}
