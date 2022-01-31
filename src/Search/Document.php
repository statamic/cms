<?php

namespace Statamic\Search;

use Statamic\Data\ContainsData;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Document
{
    use ContainsData, FluentlyGetsAndSets;

    protected $reference;

    public function reference()
    {
        return $this->fluentlyGetOrSet('reference')->args(func_get_args());
    }
}
