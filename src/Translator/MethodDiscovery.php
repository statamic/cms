<?php

namespace Statamic\Translator;

use Illuminate\Filesystem\Filesystem;

class MethodDiscovery
{
    protected $files;
    protected $paths;

    public function __construct(Filesystem $files, array $paths)
    {
        $this->files = $files;
        $this->paths = $paths;
    }

    public function discover()
    {
        $strings = [];

        foreach ($this->files->allFiles($this->paths) as $file) {
            if (preg_match_all('/' . $this->regex() . '/imu', $file->getContents(), $matches)) {
                foreach ($matches[2] as $match) {
                    $strings[] = trim(stripcslashes($match));
                }
            }
        }

        return collect($strings);
    }

    protected function regex()
    {
        return '(trans(?:_choice)?|__n?)'
            . '\([\'"`]'
            . '([\w\d\s\t\n\r,.\'\":\\\?!@£$%^&*<>_\-=\|\+]+)'
            . '[\'\"`]';
    }
}