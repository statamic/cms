<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Concerns\TracksAuthenticatedUser;

class FieldsetSaved extends Event implements ProvidesCommitMessage
{
    use TracksAuthenticatedUser;

    public $fieldset;

    public function __construct($fieldset)
    {
        $this->fieldset = $fieldset;
    }

    public function commitMessage()
    {
        return __('Fieldset saved', [], config('statamic.git.locale'));
    }
}
