<?php

namespace Statamic\Http\Controllers\CP\Assets;

trait ExtractsFromAssetFields
{
    protected function extractFromFields($asset, $blueprint)
    {
        $values = $asset->data();

        $fields = $blueprint
            ->fields()
            ->addValues($values->all())
            ->preProcess();

        return [$fields->values()->all(), $fields->meta()->all()];
    }
}
