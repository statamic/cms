<?php

namespace Statamic\View\Debugbar\AntlersProfiler;

class RuntimeSample
{
    /**
     * The UTC timestamp when the sample was taken.
     *
     * @var int
     */
    public $time = 0;

    /**
     * The relative system memory usage when the sample was taken.
     *
     * @var int
     */
    public $memory = 0;

    /**
     * The number of Antlers nodes processed when the sample was taken.
     *
     * @var int
     */
    public $antlersNodesProcessed = 0;
}
