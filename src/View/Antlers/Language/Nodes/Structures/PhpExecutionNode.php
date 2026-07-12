<?php

namespace Statamic\View\Antlers\Language\Nodes\Structures;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;

class PhpExecutionNode extends AbstractNode
{
    public $isEchoNode = false;
    public $rawStart = '';
    public $rawEnd = '';
}
