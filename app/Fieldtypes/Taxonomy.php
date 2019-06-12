<?php

namespace Statamic\Fieldtypes;

use Statamic\API;
use Statamic\API\Arr;
use Statamic\API\Term;
use Statamic\Fields\Fieldtype;
use Statamic\Data\Taxonomies\TermCollection;

class Taxonomy extends Fieldtype
{
    protected $component = 'tags';

    public function augment($value, $entry = null)
    {
        $taxonomy = API\Taxonomy::findByHandle($this->config('taxonomy'));
        $collection = $entry->collection();

        return (new TermCollection(Arr::wrap($value)))
            ->map(function ($slug) use ($taxonomy, $collection) {
                return Term::make($slug)
                    ->taxonomy($taxonomy)
                    ->collection($collection);
            });
    }
}
