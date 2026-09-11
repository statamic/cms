<?php

namespace Statamic\Stache\Repositories;

use Illuminate\Support\Collection;
use Statamic\Contracts\Taxonomies\Taxonomy;
use Statamic\Contracts\Taxonomies\TaxonomyRepository as RepositoryContract;
use Statamic\Exceptions\TaxonomyNotFoundException;
use Statamic\Facades;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Stache\Stache;
use Statamic\Support\Str;

class TaxonomyRepository implements RepositoryContract
{
    protected $store;
    protected $additionalPreviewTargets = [];

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

    public function findOrFail($id): Taxonomy
    {
        $taxonomy = $this->find($id);

        if (! $taxonomy) {
            throw new TaxonomyNotFoundException($id);
        }

        return $taxonomy;
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

    public function findByUri(string $uri, ?string $site = null): ?Taxonomy
    {
        $site = $site ?? Site::current()->handle();
        $uri = URL::tidy(Str::ensureLeft($uri, '/'));

        if ($taxonomy = $this->findByRoute($uri, $site)) {
            return $taxonomy;
        }

        [$collection, $stripped] = $this->stripCollectionPrefix($uri, $site);

        if (! $collection) {
            return null;
        }

        return $this->findByRoute($stripped, $site, automagicOnly: true)?->collection($collection);
    }

    private function findByRoute(string $uri, string $site, bool $automagicOnly = false): ?Taxonomy
    {
        return $this->all()->first(function ($taxonomy) use ($uri, $site, $automagicOnly) {
            if ($automagicOnly && $taxonomy->hasCustomRoutes()) {
                return false;
            }

            $route = $taxonomy->taxonomyRoute($site);

            return $route && URL::tidy($route) === $uri;
        });
    }

    private function stripCollectionPrefix(string $uri, string $site): array
    {
        $collection = Facades\Collection::all()
            ->first(function ($collection) use ($uri, $site) {
                if (Str::startsWith($uri, $collection->uri($site))) {
                    return true;
                }

                return Str::startsWith($uri.'/', '/'.$collection->handle().'/');
            });

        if (! $collection) {
            return [null, $uri];
        }

        $stripped = Str::after($uri, $collection->uri($site) ?? $collection->handle());

        // If the collection is mounted to the home page, the uri would have
        // the slash trimmed off at this point. We'll make sure it's there.
        $stripped = URL::tidy(Str::ensureLeft($stripped, '/'));

        return [$collection, $stripped];
    }

    public static function bindings(): array
    {
        return [
            Taxonomy::class => \Statamic\Taxonomies\Taxonomy::class,
        ];
    }

    public function addPreviewTargets($handle, $targets)
    {
        $targets = collect($this->additionalPreviewTargets[$handle] ?? [])
            ->merge($targets)
            ->unique(function ($target) {
                return $target['format'];
            })->all();

        $this->additionalPreviewTargets = array_merge($this->additionalPreviewTargets, [$handle => $targets]);
    }

    public function additionalPreviewTargets($handle)
    {
        return collect($this->additionalPreviewTargets[$handle] ?? []);
    }
}
