<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Fields\Fields;

class ActionModalController extends CpController
{
    public function resolve(Request $request)
    {
        $fields = $this->getFields($request->fields)
            ->preProcess();

        return [
            'fieldset' => $fields->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
        ];
    }

    public function process(Request $request)
    {
        $fields = $this->getFields($request->fields)
            ->addValues($request->values);

        $fields->validate();

        $values = $fields->process()->values();

        return [
            'values' => $values,
        ];
    }

    protected function getFields($fieldItems)
    {
        $fields = collect($fieldItems)->map(function ($field, $handle) {
            return compact('handle', 'field');
        });

        return new Fields($fields);
    }
}
