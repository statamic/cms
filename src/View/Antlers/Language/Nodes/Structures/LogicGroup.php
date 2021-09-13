<?php

namespace Statamic\View\Antlers\Language\Nodes\Structures;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;

class LogicGroup extends AbstractNode
{
    /**
     * @var LogicalGroupBegin|null
     */
    public $start = null;

    /**
     * @var LogicalGroupEnd|null
     */
    public $end = null;

    /**
     * @var AbstractNode[]
     */
    public $nodes = [];
}
