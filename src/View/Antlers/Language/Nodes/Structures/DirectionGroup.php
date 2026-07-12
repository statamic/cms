<?php

namespace Statamic\View\Antlers\Language\Nodes\Structures;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;

class DirectionGroup extends AbstractNode
{
    /**
     * @var ValueDirectionNode[]
     */
    public $orderClauses = [];
}
