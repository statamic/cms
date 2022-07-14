<?php

namespace Statamic\Contracts\Search;

use Statamic\Contracts\Data\Augmentable;

interface Result extends Augmentable
{
    public function getSearchable(): Searchable;

    public function getReference(): string;

    public function getScore(): int;

    public function setScore(int $score);

    public function getType(): string;

    public function setType(string $type): self;

    public function getCpTitle(): string;

    public function getCpUrl(): string;

    public function getCpBadge(): string;
}
