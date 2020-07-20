<?php

namespace Statamic\Events;

class TaxonomyDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Taxonomy deleted');
    }
}
