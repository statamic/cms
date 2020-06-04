<?php

namespace Statamic\Stache\Repositories;

use Illuminate\Support\Collection;
use Statamic\Contracts\Structures\Nav;
use Statamic\Contracts\Structures\NavigationRepository as RepositoryContract;
use Statamic\Stache\Stache;

class NavigationRepository implements RepositoryContract
{
    protected $stache;
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('navigation');
    }

    public function all(): Collection
    {
        $keys = $this->store->paths()->keys();

        return $this->store->getItems($keys);
    }

    public function find($id): ?Nav
    {
        return $this->findByHandle($id);
    }

    public function findByHandle($handle): ?Nav
    {
        return $this->store->getItem($handle);
    }

    public function save(Nav $nav)
    {
        $this->store->save($nav);
    }

    public function delete(Nav $nav)
    {
        $this->store->delete($nav);
    }

    public function make(string $handle = null): Nav
    {
        return app(Nav::class)->handle($handle);
    }

    public function updateEntryUris(Nav $nav)
    {
        $this->store->index('uri')->updateItem($nav);
    }

    public static function bindings(): array
    {
        return [
            Nav::class => \Statamic\Structures\Nav::class,
        ];
    }
}
