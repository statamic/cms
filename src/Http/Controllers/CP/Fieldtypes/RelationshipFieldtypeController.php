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
        $config = $this->getConfig($request);

        return Fieldtype::find($config['type'])->setField(
            new Field('relationship', $config)
        );
    }

    private function getConfig($request)
    {
        // The fieldtype base64-encodes the config.
        $json = base64_decode($request->config);

        // The json may include unicode characters, so we'll try to convert it to UTF-8.
        // See https://github.com/statamic/cms/issues/566
        $utf8 = mb_convert_encoding($json, 'UTF-8', mb_list_encodings());

        // In PHP 8.1 there's a bug where encoding will return null. It's fixed in 8.1.2.
        // In this case, we'll fall back to the original JSON, but without the encoding.
        // Issue #566 may still occur, but it's better than failing completely.
        $json = empty($utf8) ? $json : $utf8;

        return json_decode($json, true);
    }
}
