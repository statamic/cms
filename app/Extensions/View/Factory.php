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
        'antlers.html' => 'antlers',
        'antlers.php' => 'antlers',
        'blade.php' => 'blade',
        'php' => 'php',
        'css' => 'file',
    ];
}
