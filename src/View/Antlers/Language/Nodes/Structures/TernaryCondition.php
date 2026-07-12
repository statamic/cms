<?php

namespace Statamic\View\Antlers\Language\Nodes\Structures;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;

class TernaryCondition extends AbstractNode
{
    /**
     * @var AbstractNode|null
     */
    public $head = null;

    /**
     * @var AbstractNode|null
     */
    public $truthBranch = null;

    /**
     * @var AbstractNode|null
     */
    public $falseBranch = null;
}
