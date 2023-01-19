<?php

namespace Statamic\Search;

use Illuminate\Support\Collection;

interface ProvidesSearchables
{
    public function setKeys(array $keys): self;

    public function provide(): Collection;

    public function contains($searchable): bool;

    public function find(array $keys): Collection;

    public function referencePrefix(): string;
}
