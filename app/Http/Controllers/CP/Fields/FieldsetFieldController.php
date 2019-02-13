<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Statamic\API;
use Illuminate\Http\Request;
use Statamic\Fields\Fieldset;
use Statamic\Http\Controllers\CP\CpController;

class FieldsetFieldController extends CpController
{
    public function store(Request $request, $fieldset)
    {
        $this->authorize('create', Fieldset::class);

        $fieldset = API\Fieldset::find($fieldset);
        $field = $request->all();
        $handle = array_pull($field, 'handle');

        $contents = $fieldset->contents();
        $fields = $contents['fields'];
        $fields[$handle] = array_except($field, '_id');
        $contents['fields'] = $fields;

        $fieldset->setContents($contents)->save();

        return array_merge($fieldset->field($handle)->toBlueprintArray(), [
            'fieldset' => [
                'handle' => $fieldset->handle(),
                'title' => $fieldset->title(),
            ]
        ]);
    }
}
