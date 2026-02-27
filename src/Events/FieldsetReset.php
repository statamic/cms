<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class FieldsetReset extends Event implements ProvidesCommitMessage
{
    public function __construct(public $fieldset)
    {
    }

    public function commitMessage()
    {
        return __('Fieldset reset', [], config('statamic.git.locale'));
    }
}
