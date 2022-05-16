<?php

namespace Statamic\View\Antlers\Language\Nodes;

use Statamic\View\Antlers\Language\Nodes\Paths\VariableReference;

class VariableNode extends AbstractNode
{
    public $name = '';

    /**
     * @var VariableReference|null
     */
    public $variableReference = null;
    public $interpolationNodes = null;
    public $isInterpolationReference = false;
}
