<?php

namespace Statamic\Structures;

use Statamic\Entries\AugmentedEntry;
use Statamic\Statamic;

class AugmentedPage extends AugmentedEntry
{
    protected $page;
    protected $hasEntry = false;
    private $cachedKeys;
    private $fieldsCache;

    public function __construct($page)
    {
        $this->page = $page;

        if ($page->reference() && $page->referenceExists()) {
            $this->hasEntry = true;
            parent::__construct($page->entry());
        } else {
            parent::__construct($page);
        }
    }

    public function keys()
    {
        if ($this->cachedKeys) {
            return $this->cachedKeys;
        }

        $keys = collect($this->hasEntry
            ? parent::keys()
            : ['title', 'url', 'uri', 'permalink', 'id']);

        $keys = $keys
            ->merge($this->page->data()->keys())
            ->merge($this->page->supplements()->keys())
            ->merge(['entry_id']);

        $keys = Statamic::isApiRoute() ? $this->apiKeys($keys) : $keys;

        return $this->cachedKeys = $keys->unique()->sort()->values()->all();
    }

    private function apiKeys($keys)
    {
        return collect($keys)
            ->reject(function ($key) {
                return in_array($key, ['parent']);
            });
    }

    protected function getFromData($key)
    {
        if ($key === 'title') {
            return $this->page->title();
        }

        return $this->page->getSupplement($key) ?? $this->page->value($key);
    }

    public function blueprintFields()
    {
        if ($this->fieldsCache) {
            return $this->fieldsCache;
        }

        $fields = ($pageBlueprint = $this->page->blueprint())
            ? $pageBlueprint->fields()->all()
            : collect();

        if ($this->page !== $this->data) {
            $entryFields = $this->data->blueprint()->fields()->all();
            $fields = $entryFields->merge($fields);
        }

        return $this->fieldsCache = $fields;
    }

    protected function id()
    {
        return $this->page->id();
    }

    protected function entryId()
    {
        return $this->page->reference();
    }
}
