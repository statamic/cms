<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Statamic\Fields\Field;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Statamic\Http\Controllers\CP\CpController;
use Facades\Statamic\Fields\FieldtypeRepository as Fieldtype;

class RelationshipFieldtypeController extends CpController
{
    public function index(Request $request)
    {
        $fieldtype = $this->fieldtype($request);

        $items = $fieldtype->getIndexItems($request);

        if ($items instanceof Collection) {
            $items = $fieldtype->filterExcludedItems($items, $request->exclusions ?? []);
        }

        return $fieldtype->getResourceCollection($request, $items);
    }

    public function data(Request $request)
    {
        $fieldtype = $this->fieldtype($request);

        $items = $fieldtype
            ->getItemData($request->selections)
            ->values();

        return $fieldtype->getResourceCollection($request, $items);
    }

    protected function fieldtype($request)
    {
        $config = json_decode(utf8_encode(base64_decode($request->config)), true);

        return Fieldtype::find($config['type'])->setField(
            new Field('relationship', $config)
        );
    }
}
