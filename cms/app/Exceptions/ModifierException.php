<?php

namespace Statamic\Exceptions;

class ModifierException extends \Exception
{
    protected $modifier_name;

    public function setModifier($modifier_name)
    {
        $this->modifier_name = $modifier_name;
    }

    public function getModifier()
    {
        return $this->modifier_name;
    }
}