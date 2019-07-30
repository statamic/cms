<?php

namespace Statamic\Stache\Repositories;

use Statamic\Stache\Stache;
use Illuminate\Support\Collection;
use Statamic\Contracts\Data\Taxonomies\Taxonomy;
use Statamic\Contracts\Data\Repositories\TaxonomyRepository as RepositoryContract;

class TaxonomyRepository implements RepositoryContract
{
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->store = $stache->store('taxonomies');
    }

    public function all(): Collection
    {
        return $this->store->getItems();
    }

    public function findByHandle($handle): ?Taxonomy
    {
        return $this->store->getItem($handle);
    }

    public function save(Taxonomy $taxonomy)
    {
        $this->store->setItem($taxonomy->handle(), $taxonomy);

        $this->store->save($taxonomy);
    }

    public function make($handle = null)
    {
        return app(Taxonomy::class)->handle($handle);
    }
}
