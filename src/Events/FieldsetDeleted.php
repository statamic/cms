<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class FieldsetDeleted extends Event implements ProvidesCommitMessage
{
    public $fieldset;
    public $currentUser;

    public function __construct($fieldset, $currentUser = null)
    {
        $this->fieldset = $fieldset;
        $this->currentUser = $currentUser;
    }

    public function commitMessage()
    {
        return __('Fieldset deleted', [], config('statamic.git.locale'));
    }
}
