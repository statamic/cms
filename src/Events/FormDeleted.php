<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class FormDeleted extends Event implements ProvidesCommitMessage
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
        return __('Form deleted', [], config('statamic.git.locale'));
    }
}
