<?php

namespace Statamic\Events\Data;

class TaxonomyDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Taxonomy deleted');
    }
}
