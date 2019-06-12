<?php

namespace Statamic\Stache\Repositories;

use Statamic\API\Str;
use Statamic\Stache\Stache;
use Statamic\API\Collection;
use Statamic\Data\Taxonomies\QueryBuilder;
use Statamic\Data\Taxonomies\TermCollection;
use Statamic\Contracts\Data\Taxonomies\Term;
use Statamic\Contracts\Data\Repositories\TermRepository as RepositoryContract;

class TermRepository implements RepositoryContract
{
    protected $stache;
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('terms');
    }

    public function all(): TermCollection
    {
        return new TermCollection($this->store->getItems()->mapWithKeys(function ($item) {
            return $item;
        }));
    }

    public function whereTaxonomy(string $handle): TermCollection
    {
        return new TermCollection($this->store->store($handle)->getItems());
    }

    public function whereInTaxonomy(array $handles): TermCollection
    {
        return (new TermCollection($handles))->flatMap(function ($taxonomy) {
            return $this->whereTaxonomy($taxonomy);
        });
    }

    public function find($id): ?Term
    {
        if (! $store = $this->store->getStoreById($id)) {
            return null;
        }


        return $store->getItem($id);
    }

    public function findByUri(string $uri, string $site = null): ?Term
    {
        $collection = Collection::all()
            ->filter->url()
            ->first(function ($collection) use ($uri) {
                return Str::startsWith($uri, $collection->url());
            });

        if ($collection) {
            $uri = Str::after($uri, $collection->url());
        }

        if (! $term = $this->find($this->store->getIdFromUri($uri, $site))) {
            return null;
        }

        return $term->collection($collection);
    }

    public function findBySlug(string $slug, string $collection): ?Term
    {
        $store = $this->store->store($collection);

        return $store->getItems()->first(function ($term) use ($slug) {
            return $term->slug() === $slug;
        });
    }

    public function save($term)
    {
        $this->store
            ->store($term->taxonomyHandle())
            ->insert($term);

        $this->store->save($term);
    }

    public function delete($entry)
    {
        $this->store->remove($entry->id());

        $this->store->delete($entry);
    }

    public function query()
    {
        return new QueryBuilder;
    }

    public function make($slug = null): Term
    {
        return (new \Statamic\Data\Taxonomies\Term)->slug($slug);
    }
}
