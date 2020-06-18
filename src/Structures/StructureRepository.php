<?php

namespace Statamic\Structures;

use Illuminate\Support\Collection as IlluminateCollection;
use Statamic\Contracts\Structures\Structure;
use Statamic\Contracts\Structures\StructureRepository as RepositoryContract;
use Statamic\Facades\Collection;
use Statamic\Facades\Nav;
use Statamic\Support\Str;

class StructureRepository implements RepositoryContract
{
    public function all(): IlluminateCollection
    {
        return Nav::all()
            ->merge(Collection::whereStructured()->map->structure());
    }

    public function find($id): ?Structure
    {
        return $this->findByHandle($id);
    }

    public function findByHandle($handle): ?Structure
    {
        if (Str::startsWith($handle, 'collection::')) {
            return Collection::find(Str::after($handle, 'collection::'))->structure();
        }

        return Nav::find($handle);
    }

    public function save(Structure $structure)
    {
        $this->store->save($structure);
    }

    public function delete(Structure $structure)
    {
        $this->store->delete($structure);
    }

    public static function bindings(): array
    {
        return [];
    }
}
