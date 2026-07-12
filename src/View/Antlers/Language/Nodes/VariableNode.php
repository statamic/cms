<?php

namespace Statamic\View\Antlers\Language\Nodes;

use Statamic\View\Antlers\Language\Nodes\Paths\VariableReference;

class VariableNode extends AbstractNode
{
    public $name = '';

    public function getVariableContent()
    {
        if (strlen($this->content) == 0) {
            return $this->name;
        }

        return $this->content;
    }

    /**
     * @var VariableReference|null
     */
    public $variableReference = null;
    public $interpolationNodes = null;
    public $isInterpolationReference = false;
}
