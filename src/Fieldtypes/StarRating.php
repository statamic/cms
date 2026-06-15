<?php

namespace Statamic\Fieldtypes;

use Statamic\Fieldtypes\Concerns\HasStarRatingSettings;
use Statamic\Fields\Fieldtype;

class StarRating extends Fieldtype
{
    use HasStarRatingSettings;

    protected $selectable = false;

    public function preload(): array
    {
        return $this->starRatingSettings();
    }
}
