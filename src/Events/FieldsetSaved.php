<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class FieldsetSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $fieldset)
    {
    }

    public function commitMessage()
    {
        return __('Fieldset saved', [], config('statamic.git.locale'));
    }
}
