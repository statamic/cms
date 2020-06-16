<?php

namespace Statamic\Stache\Repositories;

use Illuminate\Support\Collection;
use Statamic\Contracts\Taxonomies\Taxonomy;
use Statamic\Contracts\Taxonomies\TaxonomyRepository as RepositoryContract;
use Statamic\Facades;
use Statamic\Facades\Site;
use Statamic\Stache\Stache;
use Statamic\Support\Str;

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

    public function find($id): ?Taxonomy
    {
        return $this->findByHandle($id);
    }

    public function handles(): Collection
    {
        return $this->store->paths()->keys();
    }

    public function handleExists(string $handle): bool
    {
        return $this->handles()->contains($handle);
    }

    public function findByHandle($handle): ?Taxonomy
    {
        return $this->store->getItem($handle);
    }

    public function save(Taxonomy $taxonomy)
    {
        $this->store->save($taxonomy);
    }

    public function delete(Taxonomy $taxonomy)
    {
        $this->store->delete($taxonomy);
    }

    public function make(?string $handle = null): Taxonomy
    {
        return app(Taxonomy::class)->handle($handle);
    }

    public function findByUri(string $uri, string $site = null): ?Taxonomy
    {
        $collection = Facades\Collection::all()
            ->first(function ($collection) use ($uri) {
                if (Str::startsWith($uri, $collection->url())) {
                    return true;
                }

                return Site::hasMultiple() ? false : Str::startsWith($uri, '/'.$collection->handle());
            });

        if ($collection) {
            $uri = Str::after($uri, $collection->url() ?? $collection->handle());
        }

        // If the collection is mounted to the home page, the uri would have
        // the slash trimmed off at this point. We'll make sure it's there.
        $uri = Str::ensureLeft($uri, '/');

        if (! $key = $this->store->index('uri')->items()->flip()->get($uri)) {
            return null;
        }

        return $this->findByHandle($key)->collection($collection);
    }

    public static function bindings(): array
    {
        return [
            Taxonomy::class => \Statamic\Taxonomies\Taxonomy::class,
        ];
    }
}
