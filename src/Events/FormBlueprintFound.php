<?php

namespace Statamic\Events;

class FormBlueprintFound extends Event
{
    public $blueprint;
    public $form;

    public function __construct($blueprint, $form = null)
    {
        $this->blueprint = $blueprint;
        $this->form = $form;
    }
}
