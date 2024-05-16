<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Statamic\Facades\Action;
use Statamic\Facades\Term;
use Statamic\Http\Controllers\CP\ActionController;

class TermActionController extends ActionController
{
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

    protected function extractFromFields($term, $blueprint)
    {
        // The values should only be data merged with the origin data.
        // We don't want injected taxonomy values, which $term->values() would have given us.
        $values = $term->inDefaultLocale()->data()->merge(
            $term->data()
        );

        $fields = $blueprint
            ->fields()
            ->addValues($values->all())
            ->preProcess();

        $values = $fields->values()->merge([
            'title' => $term->value('title'),
            'slug' => $term->slug(),
        ]);

        return [$values->all(), $fields->meta()];
    }
}
