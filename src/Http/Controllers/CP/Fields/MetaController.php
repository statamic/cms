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

        $value = $field->fieldtype()->preProcess($request->value);

        $field->setValue($value);

        return [
            'value' => $value,
            'meta' => $field->fieldtype()->preload(),
        ];
    }
}
