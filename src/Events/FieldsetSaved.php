<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

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
