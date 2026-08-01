<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\Path;

class EntriesStore extends AggregateStore
{
    protected $childStore = CollectionEntriesStore::class;

    protected $customDirectories = [];

    public function key()
    {
        return 'entries';
    }

    public function discoverStores()
    {
        return \Statamic\Facades\Collection::handles()->map(function ($handle) {
            return $this->store($handle);
        });
    }

    public function setCustomDirectory(string $handle, ?string $directory): self
    {
        if ($directory) {
            $this->customDirectories[$handle] = $directory;
        } else {
            unset($this->customDirectories[$handle]);
        }

        return $this;
    }

    public function customDirectory(string $handle): ?string
    {
        return $this->customDirectories[$handle] ?? null;
    }

    public function childDirectory($child)
    {
        $handle = $child->childKey();

        if ($directory = $this->customDirectory($handle)) {
            return Path::tidy(
                Path::isAbsolute($directory) ? $directory : base_path($directory)
            );
        }

        return parent::childDirectory($child);
    }
}
