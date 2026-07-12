<?php

namespace Statamic\View\Antlers\Language\Runtime\Debugging;

class StackFrame
{
    public $line = 0;

    public $column = 0;

    public $id = 0;

    public $name = '';

    public $pathSource = '';
}
