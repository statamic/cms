<?php

namespace Statamic\Events\Data;

class TaxonomySaved extends Saved
{
    public function commitMessage()
    {
        return __('Taxonomy saved');
    }
}
