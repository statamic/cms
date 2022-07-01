<?php

namespace Statamic\View\Antlers\Language\Nodes\Structures;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;

class NullCoalescenceGroup extends AbstractNode
{
    /**
     * @var AbstractNode|null
     */
    public $left = null;

    /**
     * @var AbstractNode|null
     */
    public $right = null;
}
