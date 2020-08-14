<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class TaxonomySaved extends Event implements ProvidesCommitMessage
{
    public $taxonomy;

    public function __construct($taxonomy)
    {
        $this->taxonomy = $taxonomy;
    }

    public function commitMessage()
    {
        return __('Taxonomy saved');
    }
}
