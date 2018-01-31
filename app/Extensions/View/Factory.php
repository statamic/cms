<?php

namespace Statamic\Extensions\View;

use Illuminate\View\Factory as ViewFactory;

class Factory extends ViewFactory
{
    /**
     * The extension to engine bindings.
     *
     * @var array
     */
    protected $extensions = [
        'blade.php' => 'blade',
        'php' => 'php',
        'antlers.html' => 'antlers'
    ];
}
