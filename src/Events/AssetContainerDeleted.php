<?php

namespace Statamic\Events;

class AssetContainerDeleted extends Deleted
{
    public $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function commitMessage()
    {
        return __('Asset container deleted');
    }
}
