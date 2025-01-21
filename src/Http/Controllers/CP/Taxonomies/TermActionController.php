<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Statamic\Facades\Action;
use Statamic\Facades\Term;
use Statamic\Http\Controllers\CP\ActionController;
use Statamic\Http\Resources\CP\Taxonomies\Term as TermResource;

class TermActionController extends ActionController
{
    use ExtractsFromTermFields;

    protected function getSelectedItems($items, $context)
    {
        return $items->map(function ($item) {
            return Term::find($item);
        })->filter();
    }

    protected function getItemData($term, $context): array
    {
        $term = $term->fresh();

        $blueprint = $term->blueprint();

        [$values] = $this->extractFromFields($term, $blueprint);

        return array_merge((new TermResource($term))->resolve()['data'], [
            'values' => $values,
            'itemActions' => Action::for($term, $context),
        ]);
    }
}
