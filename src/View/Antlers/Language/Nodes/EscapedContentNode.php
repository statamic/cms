<?php

namespace Statamic\View\Antlers\Language\Nodes;

class EscapedContentNode extends AntlersNode
{
    /**
     * Indicates if the node was the result of a noparse region.
     *
     * @var bool
     */
    public $isNoParseRegion = false;
}
