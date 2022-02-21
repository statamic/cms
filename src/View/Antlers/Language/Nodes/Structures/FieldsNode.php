<?php

namespace Statamic\View\Antlers\Language\Nodes\Structures;

use Statamic\View\Antlers\Language\Nodes\AbstractNode;
use Statamic\View\Antlers\Language\Nodes\StringValueNode;

class FieldsNode extends AbstractNode
{
    /** @var GroupByField[] */
    public $fields = [];
    public $isNamedNode = false;
    /**
     * @var StringValueNode|null
     */
    public $parsedName = null;
}
