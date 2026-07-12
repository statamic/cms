<?php

namespace Statamic\View\Antlers\Language\Nodes\Structures;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\NameValueNode;

class ArrayNode extends AbstractNode
{
    /**
     * @var NameValueNode[]
     */
    public $nodes = [];
}
