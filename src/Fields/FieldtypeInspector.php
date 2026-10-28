<?php

namespace Statamic\Fields;

use BackedEnum;
use Illuminate\Support\Collection;
use Statamic\Statamic;

class FieldtypeInspector
{
    public function __construct(private FieldtypeRepository $fieldtypes)
    {
    }

    public function all(): array
    {
        return [
            'statamic_version' => Statamic::version(),
            'fieldtypes' => $this->fieldtypes->classes()
                ->sortKeys()
                ->map(fn ($class, $handle) => $this->metadata($handle, app($class)))
                ->values()->all(),
        ];
    }

    public function show(string $handle): array
    {
        $fieldtype = $this->fieldtypes->find($handle);

        return [
            'statamic_version' => Statamic::version(),
            'fieldtype' => $this->metadata($handle, $fieldtype),
            'config' => $this->definitions($fieldtype->configFields()),
            'common' => $this->definitions(Field::commonFieldOptions()),
        ];
    }

    private function metadata(string $handle, Fieldtype $fieldtype): array
    {
        return [
            'handle' => $handle,
            'class' => $fieldtype::class,
            'title' => $fieldtype->title(),
            'selectable' => $fieldtype->selectable(),
            'selectable_in_forms' => $fieldtype->selectableInForms(),
            'localizable' => $fieldtype->localizable(),
            'validatable' => $fieldtype->validatable(),
            'defaultable' => $fieldtype->defaultable(),
        ];
    }

    private function definitions(Fields $fields): array
    {
        return $fields->all()
            ->map(fn (Field $field) => $this->definition($field))
            ->all();
    }

    private function definition(Field $field): array
    {
        $definition = $field->config();
        $editorType = $definition['type'] ?? null;
        unset($definition['type']);

        $definition = ['editor_type' => $editorType] + $definition;

        if (
            ! array_key_exists('options', $definition)
            && in_array($editorType, ['bard_buttons_setting', 'markdown_buttons_setting'], true)
        ) {
            $definition['options'] = [
                'status' => 'unresolved',
                'source' => 'editor',
            ];
        }

        return $this->normalize($definition);
    }

    private function normalize(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof Collection) {
            $value = $value->all();
        }

        if (is_array($value)) {
            return array_map(fn ($item) => $this->normalize($item), $value);
        }

        if (is_object($value) || is_resource($value)) {
            return [
                'status' => 'unresolved',
                'php_type' => get_debug_type($value),
            ];
        }

        return $value;
    }
}
