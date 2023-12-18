<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class FormSaved extends Event implements ProvidesCommitMessage
{
    public $form;
    public $currentUser;

    public function __construct($form, $currentUser = null)
    {
        $this->form = $form;
        $this->currentUser = $currentUser;
    }

    public function commitMessage()
    {
        return __('Form saved', [], config('statamic.git.locale'));
    }
}
