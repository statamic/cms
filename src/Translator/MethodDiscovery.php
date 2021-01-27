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
            if (preg_match_all('/'.$this->methodRegex().'/imu', $file->getContents(), $matches)) {
                foreach ($matches[2] as $match) {
                    $strings[] = trim(stripcslashes($match));
                }
            }

            if (preg_match_all('/'.$this->annotatedReturnRegex().'/imu', $file->getContents(), $matches)) {
                foreach ($matches[2] as $match) {
                    $strings[] = trim(stripcslashes($match));
                }
            }
        }

        return collect($strings);
    }

    protected function methodRegex()
    {
        return '(trans(?:_choice)?|__n?)'
            .'\([\'"`]'
            .'([\w\d\s\t\n\r,.\'\":\\\?!@£$%^&*<>_\-=\/\|\+]+)'
            .'[\'\"`]';
    }

    protected function annotatedReturnRegex()
    {
        return '\/\**? @translation \*\/\s+'
            .'return ([\'"])([\w\d\s\t\n\r,.\'\":\\\?!@£$%^&*<>_\-=\/\|\+]+)\1;';
    }
}
