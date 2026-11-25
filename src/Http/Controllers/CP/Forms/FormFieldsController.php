<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Facades\Statamic\Forms\Fields\FormFieldtypeRepository;
use Illuminate\Http\Request;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Fieldset;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\FieldTransformer;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Forms\Fieldtypes\Fallback;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;

use function Statamic\trans as __;

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
            'id' => 'nullable',
            'fields' => 'sometimes|array',
        ]);

        $fieldtype = FormFieldtypeRepository::find($request->type);

        $blueprint = $this->blueprint($fieldtype->configBlueprint(), nested: $request->has('fields'));

        $fields = $blueprint->fields()->addValues($request->values);

        if ($request->has('fields')) {
            $this->validateNestedField($request, $fields);
        }

        $values = $fields
            ->process()
            ->values()
            ->all();

        $values = array_merge($request->values, $values);

        return [
            'values' => $values,
            'preview' => $this->fieldtypePreview($fieldtype, $values),
        ];
    }

    private function validateNestedField(Request $request, Fields $fields): void
    {
        $handle = $request->values['handle'] ?? null;

        $siblingHandles = collect($request->fields)
            ->when($request->filled('id'), fn ($siblings) => $siblings->reject(fn ($field) => ($field['_id'] ?? null) === $request->id))
            ->flatMap(function ($field) {
                if (($field['type'] ?? null) === 'import') {
                    return Fieldset::find($field['fieldset'] ?? null)?->fields()->all()
                        ->map(fn ($imported) => ($field['prefix'] ?? '').$imported->handle())
                        ->values()
                        ->all() ?? [];
                }

                return [$field['handle'] ?? null];
            });

        $fields->validate([
            'handle' => [
                function ($attribute, $value, $fail) use ($siblingHandles, $handle) {
                    if ($siblingHandles->contains($handle)) {
                        $fail(__('statamic::validation.duplicate_field_handle', ['handle' => $value]));
                    }
                },
            ],
        ], ['handle.not_in' => __('statamic::validation.reserved')]);
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

    private function blueprint($blueprint, bool $nested = false)
    {
        $commonFields = collect(FormField::commonFieldOptions()->items());

        if ($nested) {
            $commonFields = $commonFields->map(fn (array $item) => $item['handle'] === 'handle'
                ? $this->withoutFormOnlyReservedHandles($item)
                : $item);
        }

        return Blueprint::make()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => $commonFields
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

    private function withoutFormOnlyReservedHandles(array $item): array
    {
        $item['field']['validate'] = collect($item['field']['validate'])
            ->map(fn ($rule) => is_string($rule) && str_starts_with($rule, 'not_in:')
                ? 'not_in:'.implode(',', Field::reservedHandles())
                : $rule)
            ->all();

        return $item;
    }
}
