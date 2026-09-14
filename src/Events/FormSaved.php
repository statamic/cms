<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

class FormSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $form)
    {
    }

    public function commitMessage()
    {
        return __('Form saved', [], config('statamic.git.locale'));
    }
}
