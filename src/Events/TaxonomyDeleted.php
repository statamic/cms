<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class TaxonomyDeleted extends Event implements ProvidesCommitMessage
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
        return __('Taxonomy deleted', [], config('statamic.git.locale'));
    }
}
