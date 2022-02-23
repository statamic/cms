<?php

namespace Statamic\View\Antlers\Language\Nodes\Modifiers;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\ArgumentGroup;
use Statamic\View\Antlers\Language\Nodes\ModifierNameNode;

class ModifierNode extends AbstractNode
{
    /**
     * @var ModifierNameNode|null
     */
    public $nameNode = null;

    public $name = '';

    /** @var AbstractNode[] */
    public $valueNodes = [];

    /** @var ModifierParameterNode[] */
    public $parameters = [];

    /**
     * @var ArgumentGroup|null
     */
    public $methodStyleArguments = null;

    public function getParameterValues()
    {
        $values = [];

        /** @var ModifierParameterNode $param */
        foreach ($this->parameters as $param) {
            $values[] = $param->value;
        }

        return $values;
    }
}
