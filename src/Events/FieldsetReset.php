<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class FieldsetReset extends Event implements ProvidesCommitMessage
{
    public $fieldset;

    public function __construct($fieldset)
    {
        $this->fieldset = $fieldset;
    }

    public function commitMessage()
    {
        return __('Fieldset reset', [], config('statamic.git.locale'));
    }
}
