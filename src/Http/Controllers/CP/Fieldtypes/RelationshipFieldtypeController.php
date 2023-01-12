<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Facades\Statamic\Fields\FieldtypeRepository as Fieldtype;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Statamic\Fields\Field;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Requests\FilteredRequest;

class RelationshipFieldtypeController extends CpController
{
    public function index(FilteredRequest $request)
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

        return ['data' => $items];
    }

    public function filters(Request $request)
    {
        return $this->fieldtype($request)->getSelectionFilters();
    }

    protected function fieldtype($request)
    {
        $config = json_decode(mb_convert_encoding(base64_decode($request->config), 'UTF-8', mb_list_encodings()), true);

        return Fieldtype::find($config['type'])->setField(
            new Field('relationship', $config)
        );
    }
}
