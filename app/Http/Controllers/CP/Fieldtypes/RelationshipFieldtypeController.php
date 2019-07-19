<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Statamic\Fields\Field;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\Resource;
use Statamic\Http\Controllers\CP\CpController;
use Facades\Statamic\Fields\FieldtypeRepository as Fieldtype;

class RelationshipFieldtypeController extends CpController
{
    public function index(Request $request)
    {
        $fieldtype = $this->fieldtype($request);

        return Resource::collection($fieldtype->getIndexItems($request))->additional(['meta' => [
            'sortColumn' => $fieldtype->getSortColumn($request),
        ]]);
    }

    public function data(Request $request)
    {
        $items = $this->fieldtype($request)
            ->getItemData($request->selections)
            ->values();

        return Resource::collection($items);
    }

    protected function fieldtype($request)
    {
        $config = json_decode(utf8_encode(base64_decode($request->config)), true);

        return Fieldtype::find($config['type'])->setField(
            new Field('relationship', $config)
        );
    }
}
