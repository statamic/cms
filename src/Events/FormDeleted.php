<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class FormDeleted extends Event implements ProvidesCommitMessage
{
    public $form;

    public function __construct($form)
    {
        $this->form = $form;
    }

    public function commitMessage()
    {
        return __('Form deleted', [], config('statamic.git.locale'));
    }
}
