<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Fields\Fields;

class FieldActionModalController extends CpController
{
    public function resolve(Request $request)
    {
        $fields = $this
            ->getFields($request->fields)
            ->preProcess();

        return [
            'fields' => $fields->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
        ];
    }

    public function process(Request $request)
    {
        $fields = $this
            ->getFields($request->fields)
            ->addValues($request->values);

        $fields->validate();

        $processed = $fields->process()->values();

        $fields->preProcess();

        return [
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'processed' => $processed,
        ];
    }

    protected function getFields($fieldItems)
    {
        return new Fields(
            collect($fieldItems)->map(fn ($field, $handle) => compact('handle', 'field'))
        );
    }
}
