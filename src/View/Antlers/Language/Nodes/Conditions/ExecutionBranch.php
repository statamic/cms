<?php

namespace Statamic\View\Antlers\Language\Nodes\Conditions;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;

class ExecutionBranch extends AbstractNode
{
    /**
     * @var AntlersNode|null
     */
    public $head = null;

    /**
     * @var AntlersNode|null
     */
    public $tail = null;

    /**
     * @var AbstractNode[]
     */
    public $nodes = [];
}
