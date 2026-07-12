<?php

namespace Statamic\View\Antlers\Language\Nodes\Structures;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\NameValueNode;
use Statamic\View\Antlers\Language\Nodes\StringValueNode;

class ListValueNode extends AbstractNode
{
    /**
     * @var NameValueNode[]
     */
    public $values = [];

    public $isNamedNode = false;

    /**
     * @var StringValueNode|null
     */
    public $parsedName = null;
}
