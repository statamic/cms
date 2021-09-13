<?php

namespace Statamic\View\Antlers\Language\Nodes\Modifiers;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;
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
