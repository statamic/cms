<?php

namespace Statamic\Actions;

use Statamic\API;

class MoveAsset extends Action
{
    protected static $title = 'Move';

    public function visibleTo($key, $context)
    {
        return $key === 'asset-browser';
    }

    public function run($items)
    {
        // TODO
    }
}
