<?php

namespace Statamic\Search;

use Statamic\Contracts\Search\Result;
use Statamic\Support\Str;

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

    public function toSearchResult(): Result
    {
        return new \Statamic\Search\Result($this, Str::before($this->reference(), '::'));
    }
}
