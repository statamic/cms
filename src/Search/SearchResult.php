<?php

namespace Statamic\Search;

use Statamic\Data\AugmentedCollection;
use Statamic\Data\ContainsData;

class SearchResult
{
    use ContainsData;

    public function toAugmentedCollection($keys)
    {
        return new AugmentedCollection($this->data->only($keys));
    }
}
