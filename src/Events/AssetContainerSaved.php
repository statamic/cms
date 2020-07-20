<?php

namespace Statamic\Events;

class AssetContainerSaved extends Saved
{
    public $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function commitMessage()
    {
        return __('Asset container saved');
    }
}
