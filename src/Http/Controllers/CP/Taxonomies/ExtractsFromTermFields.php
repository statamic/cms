<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

trait ExtractsFromTermFields
{
    protected function extractFromFields($term, $blueprint)
    {
        // The values should only be data merged with the origin data.
        // We don't want injected taxonomy values, which $term->values() would have given us.
        $values = $term->inDefaultLocale()->data()->merge(
            $term->data()
        )->all();

        $fields = $blueprint
            ->setParent($term)
            ->fields()
            ->addValues($values)
            ->preProcess();

        $values = $fields->values()->merge([
            'title' => $term->value('title'),
            'slug' => $term->slug(),
        ]);

        $extraValues = [
            'depth' => $term->page()?->depth(),
            'children' => $term->page()?->flattenedPages()->pluck('id')->all(),
        ];

        return [$values->all(), $fields->meta(), $extraValues];
    }
}
