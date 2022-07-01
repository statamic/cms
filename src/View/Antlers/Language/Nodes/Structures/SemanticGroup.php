<?php

namespace Statamic\View\Antlers\Language\Nodes\Structures;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;

class SemanticGroup extends AbstractNode
{
    /**
     * @var AbstractNode[]
     */
    public $nodes = [];
}
