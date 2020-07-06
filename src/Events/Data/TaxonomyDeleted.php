<?php

namespace Statamic\Events\Data;

class TaxonomyDeleted extends Deleted
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Taxonomy deleted');
    }
}
