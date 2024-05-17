<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Statamic\Facades\Action;
use Statamic\Facades\Term;
use Statamic\Http\Controllers\CP\ActionController;

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
        $blueprint = $term->blueprint();

        [$values] = $this->extractFromFields($term, $blueprint);

        return [
            'title' => $term->value('title'),
            'permalink' => $term->absoluteUrl(),
            'values' => array_merge($values, ['id' => $term->id()]),
            'itemActions' => Action::for($term, $context),
        ];
    }
}
