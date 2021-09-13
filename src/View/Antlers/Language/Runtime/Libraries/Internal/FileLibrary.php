<?php

namespace Statamic\View\Antlers\Language\Runtime\Libraries\Internal;

use Statamic\View\Antlers\Language\Runtime\Libraries\RuntimeLibrary;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;

class FileLibrary extends RuntimeLibrary
{
    protected $name = 'file';
    protected $isRuntimeProtected = true;

    public function __construct()
    {
        $this->exposedMethods = [
            'exists' => [$this->stringVar('path')],
            'dirExists' => [$this->stringVar('path')],
            'readText' => [
                $this->stringVar('path'),
            ],
            'readLines' => [
                $this->stringVar('path'),
            ],
        ];
    }

    public function exists($path)
    {
        return file_exists($path);
    }

    public function dirExists($path)
    {
        return file_exists($path) && is_dir($path);
    }

    public function readText($path)
    {
        return file_get_contents($path);
    }

    public function readLines($path)
    {
        return StringUtilities::breakByNewLine(StringUtilities::normalizeLineEndings(file_get_contents($path)));
    }
}
