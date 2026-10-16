<?php

namespace Tests\Modifiers;

use Statamic\Data\ContainsData;
use Statamic\Support\Traits\FluentlyGetsAndSets;

// Represents an object that doesn't have origins and therefore wouldn't have a "value" method.
// So a "get" method would need to be used. e.g. a form Submission.
class Item
{
    use ContainsData, FluentlyGetsAndSets;

    public function __construct($data)
    {
        $this->data($data);
    }
}
