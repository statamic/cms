<?php

namespace Statamic\Events;

class FormBlueprintFound extends Event
{
    public function __construct(public $blueprint, public $form = null)
    {
    }
}
