<?php

namespace Statamic\View\Antlers\Language\Nodes\Structures;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;

class GroupByField extends AbstractNode
{
    /**
     * @var AbstractNode|null
     */
    public $field = null;
    public $alias = null;
}
