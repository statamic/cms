<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

class TaxonomyDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public $taxonomy)
    {
    }

    public function commitMessage()
    {
        return __('Taxonomy deleted', [], config('statamic.git.locale'));
    }
}
