<?php

namespace Statamic\Extensions\View;

use Illuminate\View\FileViewFinder as LaravelFileViewFinder;

class FileViewFinder extends LaravelFileViewFinder
{
    /**
     * Register a view extension with the finder.
     *
     * @var array
     */
    protected $extensions = ['antlers.html', 'antlers.php', 'blade.php', 'php', 'css'];

    /**
     * Add a location to the finder.
     *
     * @param  string  $location
     * @return void
     */
    public function prependLocation($location)
    {
        array_unshift($this->paths, $location);
    }
}
