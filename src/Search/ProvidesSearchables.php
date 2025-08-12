<?php

namespace Statamic\Search;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

interface ProvidesSearchables
{
    public static function handle(): string;

    public static function referencePrefix(): string;

    public function setKeys(array $keys): self;

    public function provide(): Collection|LazyCollection;

    public function contains($searchable): bool;

    public function find(array $keys): Collection;

    public function includedInAll(): bool;
}
