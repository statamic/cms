<?php

namespace Statamic\Contracts\Search;

interface Searchable
{
    public function getSearchValue(string $field);

    public function getSearchReference(): string;

    public function toSearchResult(): Result;
}
