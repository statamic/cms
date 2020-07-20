<?php

namespace Statamic\Events;

class TaxonomySaved extends Saved
{
    public function commitMessage()
    {
        return __('Taxonomy saved');
    }
}
