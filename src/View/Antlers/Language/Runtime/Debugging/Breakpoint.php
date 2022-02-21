<?php

namespace Statamic\View\Antlers\Language\Runtime\Debugging;

class Breakpoint
{
    /**
     * The relative path of the breakpoint.
     *
     * @var string
     */
    public $path = '';

    /**
     * The shared debugger ID for the breakpoint file.
     *
     * @var string
     */
    public $debugId = '';

    /**
     * The breakpoint line number.
     *
     * @var int
     */
    public $line = 0;
}
