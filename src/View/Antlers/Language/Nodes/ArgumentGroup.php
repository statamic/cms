<?php

namespace Statamic\View\Antlers\Language\Nodes;

class ArgumentGroup extends AbstractNode
{
    public $args = [];
    public $hasNamedArguments = false;
    public $numberOfNamedArguments = 0;
}
