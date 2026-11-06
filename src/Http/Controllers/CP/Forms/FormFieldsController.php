<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Facades\Statamic\Forms\Fields\FormFieldtypeRepository;
use Illuminate\Http\Request;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Field;
use Statamic\Fields\FieldTransformer;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Forms\Fieldtypes\Fallback;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;

class FormFieldsController extends CpController
{
    public function edit(Request $request, $form)
    {
        $this->authorize('editFields', $form);

        $request->validate([
            'type' => 'required',
            'values' => 'array',
        ]);

        $fieldtype = FormFieldtypeRepository::find($request->type);

        $blueprint = $this->blueprint($fieldtype->configBlueprint());

        $fields = $blueprint
            ->fields()
            ->addValues($request->values)
            ->preProcess();

        if ($request->reference) {
            $fieldsetFields = FieldTransformer::fieldsetFields();
            $originConfig = $fieldsetFields[$request->reference]['config'] ?? [];

            $originFields = $blueprint
                ->fields()
                ->addValues($originConfig)
                ->preProcess();

            $originValues = Arr::except($originFields->values()->all(), 'handle');
            $originMeta = $originFields->meta()->all();
        }

        return [
            'fieldtype' => $fieldtype->toArray(),
            'blueprint' => $blueprint->toPublishArray(),
            'values' => array_merge($request->values, $fields->values()->all()),
            'meta' => $fields->meta(),
            'originValues' => $originValues ?? null,
            'originMeta' => $originMeta ?? null,
        ];
    }

    public function update(Request $request, $form)
    {
        $this->authorize('editFields', $form);

        $request->validate([
            'type' => 'required',
            'values' => 'required|array',
        ]);

        $fieldtype = FormFieldtypeRepository::find($request->type);

        $blueprint = $this->blueprint($fieldtype->configBlueprint());

        $values = $blueprint
            ->fields()
            ->addValues($request->values)
            ->process()
            ->values()
            ->all();

        $values = array_merge($request->values, $values);

        return [
            'values' => $values,
            'preview' => $this->fieldtypePreview($fieldtype, $values),
        ];
    }

    private function fieldtypePreview(FormFieldtype $fieldtype, array $config): ?array
    {
        try {
            $formField = new FormField($config['handle'] ?? 'field', ['type' => $fieldtype->handle(), ...$config]);

            $field = $fieldtype instanceof Fallback
                ? new Field($fieldtype->toArray()['handle'], ['type' => $fieldtype->toArray()['handle'], ...$config])
                : (clone $fieldtype)->setField($formField)->toField();

            $field->setValue($field->defaultValue());

            return [
                'config' => $field->toPublishArray(),
                'value' => $field->fieldtype()->preProcess($field->value()),
                'meta' => $field->fieldtype()->preload(),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function blueprint($blueprint)
    {
        return Blueprint::make()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => collect(FormField::commonFieldOptions()->items())
                                ->merge($blueprint->contents()['tabs']['main']['sections'][0]['fields'])
                                ->reverse()->unique('handle')->reverse() // Prioritize the duplicate from the fieldtype
                                ->values()
                                ->all(),
                        ],
                    ],
                ],
            ],
        ]);
    }
}
