<?php

namespace Statamic\Search;

use Illuminate\Support\Collection;

interface ProvidesSearchables
{
    public function setKeys(array $keys): static;

    public function provide(): Collection;

    public function contains($searchable): bool;

    public function isSearchable($searchable): bool;
}
