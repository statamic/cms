<?php

namespace Statamic\View\Antlers\Language\Nodes\Structures;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;

class LogicGroup extends AbstractNode
{
    /**
     * @var LogicGroupBegin|null
     */
    public $start = null;

    /**
     * @var LogicGroupEnd|null
     */
    public $end = null;

    /**
     * @var AbstractNode[]
     */
    public $nodes = [];
}
