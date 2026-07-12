<?php

namespace Statamic\View\Antlers\Language\Nodes\Structures;

use Statamic\View\Antlers\Language\Nodes\StringValueNode;

class AliasedScopeLogicGroup extends ScopedLogicGroup
{
    /**
     * @var StringValueNode|null
     */
    public $alias = null;
}
