<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class TaxonomyDeleted extends Event implements ProvidesCommitMessage
{
    public $taxonomy;

    public function __construct($taxonomy)
    {
        $this->taxonomy = $taxonomy;
    }

    public function commitMessage()
    {
        return __('Taxonomy deleted', [], config('statamic.git.locale'));
    }
}
