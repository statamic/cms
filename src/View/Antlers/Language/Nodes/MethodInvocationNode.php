<?php

namespace Statamic\View\Antlers\Language\Nodes;

class MethodInvocationNode extends AbstractNode
{
    /**
     * The method to invoke.
     *
     * @var VariableNode|null
     */
    public $method = null;

    /**
     * The arguments for the method call.
     *
     * @var ArgumentGroup|null
     */
    public $args = null;
}
