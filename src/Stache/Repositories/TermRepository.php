<?php

namespace Statamic\Stache\Repositories;

use Statamic\Support\Str;
use Statamic\Stache\Stache;
use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;
use Statamic\Stache\Query\TermQueryBuilder;
use Statamic\Taxonomies\TermCollection;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\Contracts\Taxonomies\TermRepository as RepositoryContract;

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
        return $this->query()->get();
    }

    public function whereTaxonomy(string $handle): TermCollection
    {
        return $this->query()->where('taxonomy', $handle)->get();
    }

    public function whereInTaxonomy(array $handles): TermCollection
    {
        return $this->query()->whereIn('taxonomy', $handles)->get();
    }

    public function find($id): ?Term
    {
        return $this->query()->where('id', $id)->first();
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

        $uri = Str::removeLeft($uri, '/');

        [$taxonomy, $slug] = array_pad(explode('/', $uri), 2, null);

        if (! $slug) {
            return null;
        }

        if (! Taxonomy::handleExists($taxonomy)) {
            return null;
        }

        $term = $this->query()
            ->where('slug', $slug)
            ->where('taxonomy', $taxonomy)
            ->where('site', $site)
            ->first();

        if (! $term) {
            return null;
        }

        return $term->collection($collection);
    }

    public function findBySlug(string $slug, string $taxonomy): ?Term
    {
        return $this->query()
            ->where('slug', $slug)
            ->where('taxonomy', $taxonomy)
            ->first();
    }

    public function save($term)
    {
        $this->store
            ->store($term->taxonomyHandle())
            ->save($term);
    }

    public function delete($term)
    {
        $this->store
            ->store($term->taxonomyHandle())
            ->delete($term);
    }

    public function query()
    {
        $this->ensureAssociations();

        return new TermQueryBuilder($this->store);
    }

    public function make(string $slug = null): Term
    {
        return (new \Statamic\Taxonomies\Term)->slug($slug);
    }

    protected function ensureAssociations()
    {
        Taxonomy::all()->each(function ($taxonomy) {
            $this->store->store($taxonomy->handle())->index('associations');
        });
    }
}
