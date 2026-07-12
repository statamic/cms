<?php

namespace Statamic\View\Antlers\Language\Nodes\Structures;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;

class ValueDirectionNode extends AbstractNode
{
    public $order = 0;
    /**
     * @var AbstractNode|null
     */
    public $name = null;
    public $directionNode = null;
    /**
     * @var AbstractNode|null
     */
}
