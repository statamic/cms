<?php

namespace Statamic\Contracts\Data\Repositories;

use Illuminate\Support\Collection;
use Statamic\Contracts\Data\Taxonomies\Taxonomy;

interface TaxonomyRepository
{
    public function all(): Collection;
    public function find($id): ?Taxonomy;
    public function findByHandle($handle): ?Taxonomy;
    public function findByUri(string $uri): ?Taxonomy;
    public function make(string $handle = null): Taxonomy;
    public function handles(): Collection;
}
