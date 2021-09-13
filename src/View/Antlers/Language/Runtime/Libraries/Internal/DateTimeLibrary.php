<?php

namespace Statamic\View\Antlers\Language\Runtime\Libraries\Internal;

use Carbon\Carbon;
use Statamic\View\Antlers\Language\Runtime\Libraries\RuntimeLibrary;

class DateTimeLibrary extends RuntimeLibrary
{
    protected $name = 'datetime';

    public function __construct()
    {
        $this->exposedMethods = [
            'parse' => [$this->stringVar('value')],
        ];
    }

    public function parse($value)
    {
        return Carbon::parse($value);
    }
}
