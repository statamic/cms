<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class TaxonomySaved extends Event implements ProvidesCommitMessage
{
    public $taxonomy;
    public $currentUser;

    public function __construct($taxonomy, $currentUser = null)
    {
        $this->taxonomy = $taxonomy;
        $this->currentUser = $currentUser;
    }

    public function commitMessage()
    {
        return __('Taxonomy saved', [], config('statamic.git.locale'));
    }
}
