<?php

namespace Statamic\Translator;

use Illuminate\Filesystem\Filesystem;

class MethodDiscovery
{
    protected $files;
    protected $paths;
    protected $methodRegex;

    public function __construct(Filesystem $files, array $paths)
    {
        $this->files = $files;
        $this->paths = $paths;
    }

    public function discover()
    {
        if (! $this->methodRegex) {
            throw new \Exception('A discovery method was not specified');
        }

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

    public function withStrings()
    {
        $this->methodRegex = '__n?';

        return $this;
    }

    public function withKeys()
    {
        $this->methodRegex = 'trans(?:_choice)?';

        return $this;
    }

    protected function regex()
    {
        return '('.$this->methodRegex.')'
            . '\([\'"`]'
            . '([\w\d\s\t\n\r,.\'\":\\\?!@£$%^&*<>_\-=\|\+]+)'
            . '[\'\"`]';
    }
}