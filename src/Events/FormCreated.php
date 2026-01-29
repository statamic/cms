<?php

namespace Statamic\Events;

class FormCreated extends Event
{
    public function __construct(public $form)
    {
    }
}
