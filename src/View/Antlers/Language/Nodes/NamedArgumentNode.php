<?php

namespace Statamic\View\Antlers\Language\Nodes;

class NamedArgumentNode extends AbstractNode
{
    /**
     * @var AbstractNode|null
     */
    public $name = null;

    /**
     * @var AbstractNode|null
     */
    public $value = null;
}
