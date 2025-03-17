<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class TaxonomySaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $taxonomy)
    {
    }

    public function commitMessage()
    {
        return __('Taxonomy saved', [], config('statamic.git.locale'));
    }
}
