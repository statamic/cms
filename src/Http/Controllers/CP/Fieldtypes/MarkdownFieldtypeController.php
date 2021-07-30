<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Facades\Statamic\Fields\FieldtypeRepository as Fieldtype;
use Illuminate\Http\Request;
use Statamic\Fields\Field;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;

class MarkdownFieldtypeController extends CpController
{
    public function preview(Request $request)
    {
        return $this->fieldtype($request->config)->augment($request->value);
    }

    protected function fieldtype($config)
    {
        return Fieldtype::find($config['type'])->setField(
            new Field('markdown', Arr::removeNullValues($config))
        );
    }
}
