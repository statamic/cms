<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class FormSaved extends Event implements ProvidesCommitMessage
{
    public $form;

    public function __construct($form)
    {
        $this->form = $form;
    }

    public function commitMessage()
    {
        return __('Form saved', [], config('statamic.git.locale'));
    }
}
