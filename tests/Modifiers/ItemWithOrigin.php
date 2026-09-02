<?php

namespace Tests\Modifiers;

use Statamic\Data\ContainsData;
use Statamic\Data\HasOrigin;
use Statamic\Support\Traits\FluentlyGetsAndSets;

// Represents an object that could have an origin and therefore a "value" method. e.g. an Entry.
class ItemWithOrigin
{
    use ContainsData, FluentlyGetsAndSets, HasOrigin;

    public function __construct($data, $origin = null)
    {
        $this->data($data);
        $this->origin = $origin;
    }

    public function origin($origin = null)
    {
        // Bypass the logic to load the origin. Just use what was passed in.
        return $this->origin;
    }

    public function getOriginByString($origin)
    {
        // Required by trait
    }
}
