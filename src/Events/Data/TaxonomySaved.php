<?php

namespace Statamic\Events\Data;

class TaxonomySaved extends Saved
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Taxonomy saved');
    }
}
