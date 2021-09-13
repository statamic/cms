<?php

namespace Statamic\View\Antlers\Language\Runtime\Libraries\Internal;

use Statamic\View\Antlers\Language\Runtime\Libraries\RuntimeLibrary;

class StopwatchLibrary extends RuntimeLibrary
{
    protected $name = 'stopwatch';
    protected $isRuntimeProtected = true;

    protected $exposedMethods = [
        'start' => [
            [
                self::KEY_NAME => 'name',
                self::KEY_HAS_DEFAULT => false,
                self::KEY_ACCEPTS => [self::KEY_TYPE_STRING],
            ],
            [
                self::KEY_NAME => 'label',
                self::KEY_HAS_DEFAULT => true,
                self::KEY_ACCEPTS => [self::KEY_TYPE_STRING],
                self::KEY_DEFAULT => '',
            ],
        ],
        'stop' => [
            [
                self::KEY_NAME => 'name',
                self::KEY_HAS_DEFAULT => false,
                self::KEY_ACCEPTS => [self::KEY_TYPE_STRING],
            ],
        ],
    ];

    public function start($name, $label = '')
    {
        debugbar()->startMeasure($name, $label);
    }

    public function stop($name)
    {
        debugbar()->stopMeasure($name);
    }
}
