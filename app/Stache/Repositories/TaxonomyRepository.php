<?php

namespace Statamic\Stache\Repositories;

use Statamic\API;
use Statamic\API\Str;
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
        return $this->store->getItems($this->handles());
    }

    public function handles()
    {
        return $this->store->paths()->keys();
    }

    public function findByHandle($handle): ?Taxonomy
    {
        return $this->store->getItem($handle);
    }

    public function save(Taxonomy $taxonomy)
    {
        $this->store->save($taxonomy);
    }

    public function make($handle = null)
    {
        return app(Taxonomy::class)->handle($handle);
    }

    public function findByUri(string $uri, string $site = null): ?Taxonomy
    {
        $collection = API\Collection::all()
            ->filter->url()
            ->first(function ($collection) use ($uri) {
                return Str::startsWith($uri, $collection->url());
            });

        if ($collection) {
            $uri = Str::after($uri, $collection->url());
        }

        if (! $key = $this->store->index('uri')->items()->flip()->get($uri)) {
            return null;
        }

        return $this->findByHandle($key)->collection($collection);
    }
}
