<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class FormDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public $form)
    {
    }

    public function commitMessage()
    {
        return __('Form deleted', [], config('statamic.git.locale'));
    }
}
