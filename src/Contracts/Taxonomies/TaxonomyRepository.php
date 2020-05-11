<?php

namespace Statamic\Contracts\Taxonomies;

use Illuminate\Support\Collection;

interface TaxonomyRepository
{
    public function all(): Collection;

    public function find($id): ?Taxonomy;

    public function findByHandle($handle): ?Taxonomy;

    public function findByUri(string $uri): ?Taxonomy;

    public function make(string $handle = null): Taxonomy;

    public function handles(): Collection;

    public function handleExists(string $handle): bool;
}
