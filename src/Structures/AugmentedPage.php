<?php

namespace Statamic\Structures;

use Statamic\Entries\AugmentedEntry;
use Statamic\Statamic;

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
        $keys = $this->hasEntry
            ? parent::keys()
            : ['title', 'url', 'uri', 'permalink'];

        return Statamic::isApiRoute()
            ? $this->apiKeys($keys)
            : $keys;
    }

    private function apiKeys($keys)
    {
        return collect($keys)
            ->reject(function ($key) {
                return in_array($key, ['parent']);
            })
            ->all();
    }

    protected function getFromData($key)
    {
        return $this->hasEntry
            ? parent::getFromData($key)
            : null;
    }
}
