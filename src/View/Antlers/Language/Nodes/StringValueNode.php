<?php

namespace Statamic\View\Antlers\Language\Nodes;

class StringValueNode extends AbstractNode
{
    public $value = '';
    public $sourceTerminator = '';

    public function toValueString()
    {
        return $this->sourceTerminator.$this->value.$this->sourceTerminator;
    }
}
