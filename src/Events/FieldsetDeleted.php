<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class FieldsetDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public $fieldset)
    {
    }

    public function commitMessage()
    {
        return __('Fieldset deleted', [], config('statamic.git.locale'));
    }
}
