<?php

namespace Statamic\View\Antlers\Language\Nodes;

class NameValueNode extends AbstractNode
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
