<?php

namespace Statamic\CP\ResourceIndex;

use Statamic\Contracts\CP\ResourceIndex\GroupRepository as Contract;
use Statamic\Facades\Preference;

class PreferenceGroupRepository implements Contract
{
    public function find(string $resourceIndex): ?array
    {
        $preferences = Preference::default();
        $key = $this->key($resourceIndex);

        if (! $preferences->hasPreference($key)) {
            return null;
        }

        return $preferences->get($key) ?? [];
    }

    public function save(string $resourceIndex, array $groups): void
    {
        Preference::default()
            ->set($this->key($resourceIndex), $groups)
            ->save();
    }

    public function delete(string $resourceIndex): void
    {
        Preference::default()
            ->remove($this->key($resourceIndex))
            ->save();
    }

    private function key(string $resourceIndex): string
    {
        return "resource_indexes.{$resourceIndex}.groups";
    }
}
