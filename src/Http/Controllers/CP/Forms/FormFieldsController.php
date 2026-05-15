<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Facades\Statamic\Fields\FieldRepository;
use Facades\Statamic\Fields\FieldtypeRepository;
use Facades\Statamic\Forms\Fields\FormFieldtypeRepository;
use Illuminate\Http\Request;
use Statamic\Exceptions\FormFieldtypeNotFoundException;
use Statamic\Facades\Blueprint;
use Statamic\Forms\Fields\Fallback;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;

class FormFieldsController extends CpController
{
    public function edit(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'values' => 'array',
        ]);

        $fieldtype = $this->resolveFormFieldtype($request->type);

        $blueprint = $this->blueprint($fieldtype->configBlueprint());

        $fields = $blueprint
            ->fields()
            ->addValues($request->values)
            ->preProcess();

        //        if ($request->reference) {
        //            $originFields = $blueprint
        //                ->fields()
        //                ->addValues(FieldRepository::find($request->reference)->config())
        //                ->preProcess();
        //
        //            $originValues = Arr::except($originFields->values()->all(), 'handle');
        //            $originMeta = $originFields->meta()->all();
        //        }

        return [
            'fieldtype' => $fieldtype->toArray(),
            'blueprint' => $blueprint->toPublishArray(),
            'values' => array_merge($request->values, $fields->values()->all()),
            'meta' => $fields->meta(),
            'originValues' => $originValues ?? null,
            'originMeta' => $originMeta ?? null,
        ];
    }

    public function update(Request $request)
    {
        // todo
    }

    private function resolveFormFieldtype(string $type): ?FormFieldtype
    {
        try {
            return FormFieldtypeRepository::find($type);
        } catch (FormFieldtypeNotFoundException $e) {
            return (new Fallback)->wrapping(FieldtypeRepository::find($type));
        }
    }

    private function blueprint($blueprint)
    {
        return Blueprint::make()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                ...FormField::commonFieldOptions()->items(),
                                ...$blueprint->contents()['tabs']['main']['sections'][0]['fields'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
