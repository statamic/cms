<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Illuminate\Http\Request;
use Statamic\Fields\Field;
use Statamic\Http\Controllers\CP\CpController;

class MetaController extends CpController
{
    public function show(Request $request)
    {
        $config = json_decode(base64_decode($request->config), true);

        $field = (new Field($config['handle'], $config))->setValue($request->value);

        $fieldtype = $field->fieldtype();

        $value = $fieldtype->preProcess($request->value);

        return [
            'value' => $value,
            'meta' => $fieldtype->preload(),
        ];
    }
}
