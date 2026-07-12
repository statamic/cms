<?php

namespace Statamic\View\Antlers\Language\Nodes\Structures;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;

class SwitchCase extends AbstractNode
{
    /**
     * @var LogicGroup|null
     */
    public $condition = null;

    /**
     * @var LogicGroup|null
     */
    public $expression = null;
}
