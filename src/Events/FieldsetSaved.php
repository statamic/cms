<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class FieldsetSaved extends Event implements ProvidesCommitMessage
{
    public $currentUser;

    public function __construct($fieldset, $currentUser)
    {
        $this->fieldset = $fieldset;
        $this->currentUser = $currentUser;
    }

    public function commitMessage()
    {
        return __('Fieldset saved', [], config('statamic.git.locale'));
    }
}
