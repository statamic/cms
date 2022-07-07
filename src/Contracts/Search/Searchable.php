<?php

namespace Statamic\Contracts\Search;

interface Searchable
{
    public function getSearchValue(string $field);

    public function getSearchReference(): string;

    public function setSearchScore(int $score);

    public function getCpSearchResultTitle(): string;

    public function getCpSearchResultUrl(): string;

    public function getCpSearchResultBadge(): string;
}
