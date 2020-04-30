<?php

namespace Statamic\Structures;

use Statamic\Entries\AugmentedEntry;

class AugmentedPage extends AugmentedEntry
{
    protected $hasEntry = false;

    public function __construct($page)
    {
        if ($page->reference() && $page->referenceExists()) {
            $this->hasEntry = true;
            parent::__construct($page->entry());
        } else {
            parent::__construct($page);
        }
    }

    public function keys()
    {
        if ($this->hasEntry) {
            return parent::keys();
        }

        return ['title', 'url', 'uri', 'permalink'];
    }

    protected function getFromData($key)
    {
        return $this->hasEntry
            ? parent::getFromData($key)
            : null;
    }
}
