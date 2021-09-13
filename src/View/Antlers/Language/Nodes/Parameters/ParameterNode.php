<?php

namespace Statamic\View\Antlers\Language\Nodes\Parameters;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;

class ParameterNode extends AbstractNode
{
    public $isModifierParameter = false;

    public $isVariableReference = false;

    public $name = '';

    public $value = '';

    /**
     * @var string[]
     */
    public $interpolations = [];

    /**
     * @var AntlersNode|null
     */
    public $parent = null;
}
