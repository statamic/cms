<?php

namespace Statamic\View\Antlers\Language\Nodes\Paths;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;

class PathNode extends AbstractNode
{
    public $delimiter = '';
    public $name = '';
    public $isStringVar = false;
    public $isFinal = false;
}
