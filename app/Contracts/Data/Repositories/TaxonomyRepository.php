<?php

namespace Statamic\Contracts\Data\Repositories;

use Illuminate\Support\Collection;
use Statamic\Contracts\Data\Taxonomies\Taxonomy;

interface TaxonomyRepository
{
    public function all(): Collection;
    public function findByHandle($handle): ?Taxonomy;
}
