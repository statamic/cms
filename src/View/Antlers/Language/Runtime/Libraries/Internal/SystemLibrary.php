<?php

namespace Statamic\View\Antlers\Language\Runtime\Libraries\Internal;

use Statamic\Statamic;
use Statamic\View\Antlers\Language\Runtime\Libraries\RuntimeLibrary;

class SystemLibrary extends RuntimeLibrary
{
    protected $name = 'sys';
    protected $isRuntimeProtected = true;

    protected $exposedMethods = [
        'os' => 1,
        'statamicVersion' => 1,
        'time' => 1,
    ];

    public function os()
    {
        return PHP_OS;
    }

    public function time()
    {
        return time();
    }

    public function statamicVersion()
    {
        return Statamic::version();
    }
}
