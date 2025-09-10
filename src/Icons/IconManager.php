<?php

namespace Statamic\Icons;

use Illuminate\Support\Collection;

class IconManager
{
    private array $sets = [];

    public function register(string $name, string $directory): void
    {
        if ($name === 'default') {
            throw new \Exception('The name "default" is reserved.');
        }

        $this->sets[$name] = new IconSet($name, $directory);
    }

    public function sets(): Collection
    {
        return collect($this->sets);
    }

    public function get(string $name): IconSet
    {
        if ($name === 'default') {
            return $this->default();
        }

        return $this->sets[$name]
            ?? throw new \Exception('Icon set ['.$name.'] not defined');
    }

    public function default(): IconSet
    {
        return new IconSet('default', statamic_path('resources/svg/icons'));
    }
}
