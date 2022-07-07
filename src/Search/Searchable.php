<?php

namespace Statamic\Search;

trait Searchable
{
    public function getSearchReference(): string
    {
        return $this->reference();
    }

    public function getSearchValue(string $field)
    {
        return method_exists($this, $field) ? $this->$field() : $this->get($field);
    }

    public function setSearchScore(int $score = null)
    {
        $this->setSupplement('search_score', $score);
    }

    public function getCpSearchResultTitle(): string
    {
        return $this->title;
    }

    public function getCpSearchResultUrl(): string
    {
        return $this->editUrl();
    }
}
