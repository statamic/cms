<?php

namespace Statamic\View\Antlers\Language\Nodes\Structures;

class TupleScopedLogicGroup extends LogicGroup
{
    public $target = null;
    public $name = null;
    public $item1 = null;
    public $item2 = null;

    public $isDynamicNames = false;
}
