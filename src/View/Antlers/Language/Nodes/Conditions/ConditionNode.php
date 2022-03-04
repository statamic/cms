<?php

namespace Statamic\View\Antlers\Language\Nodes\Conditions;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;

class ConditionNode extends AbstractNode
{
    /**
     * The nodes that compose each branch of the condition.
     *
     * @var ExecutionBranch[]
     */
    public $logicBranches = [];

    /**
     * A reference to all node indexes that make up the chain.
     *
     * @var number[]
     */
    public $chain = [];
}
