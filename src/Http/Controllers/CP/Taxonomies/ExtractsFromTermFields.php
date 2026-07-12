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
