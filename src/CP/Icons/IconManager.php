<?php

namespace Statamic\CP\Icons;

use Illuminate\Support\Collection;

class IconManager
{
    private array $sets = [];

    public function register(string $name, string $directory): void
    {
        $this->sets[$name] = new IconSet($name, $directory);
    }

    public function all(): Collection
    {
        return collect($this->sets);
    }

    public function get(string $name): IconSet
    {
        return $this->sets[$name]
            ?? throw new \Exception('Icon set ['.$name.'] not defined');
    }

    public function has(string $name)
    {
        return array_key_exists($name, $this->sets);
    }

    public function toArray()
    {
        return $this->all()->mapWithKeys(function (IconSet $set) {
            return [$set->name() => $set->contents()];
        });
    }
}
