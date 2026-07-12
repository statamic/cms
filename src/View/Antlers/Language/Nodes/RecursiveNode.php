<?php

namespace Statamic\View\Antlers\Language\Nodes;

class RecursiveNode extends AntlersNode
{
    /**
     * @var AntlersNode|null
     */
    public $recursiveParent = null;

    public $isNestedRecursive = false;
}
