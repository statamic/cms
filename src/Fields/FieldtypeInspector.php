<?php

namespace Statamic\Fields;

use BackedEnum;
use Illuminate\Support\Collection;
use Statamic\Statamic;

/**
 * @internal
 */
class FieldtypeInspector
{
    private array $unresolved = [];

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

        $this->unresolved = [];
        $config = $this->definitions($fieldtype->configFields(), ['config']);
        $common = $this->definitions(Field::commonFieldOptions(), ['common']);

        return [
            'statamic_version' => Statamic::version(),
            'fieldtype' => $this->metadata($handle, $fieldtype),
            'config' => $config,
            'common' => $common,
            'unresolved' => $this->unresolved,
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

    private function definitions(Fields $fields, array $path): array
    {
        return $fields->all()
            ->map(fn (Field $field, $handle) => $this->definition($field, [...$path, $handle]))
            ->all();
    }

    private function definition(Field $field, array $path): array
    {
        $definition = $field->config();
        $editorType = $definition['type'] ?? null;
        unset($definition['type']);

        $definition = ['editor_type' => $editorType] + $definition;

        if (
            ! array_key_exists('options', $definition)
            && in_array($editorType, ['bard_buttons_setting', 'markdown_buttons_setting'], true)
        ) {
            $this->unresolved[] = ['path' => [...$path, 'options'], 'source' => 'editor'];
        }

        return $this->normalize($definition, $path);
    }

    private function normalize(mixed $value, array $path): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof Collection) {
            $value = $value->all();
        }

        if (is_array($value)) {
            return collect($value)
                ->map(fn ($item, $key) => $this->normalize($item, [...$path, $key]))
                ->all();
        }

        if (is_object($value) || is_resource($value)) {
            $this->unresolved[] = ['path' => $path, 'source' => 'php', 'php_type' => get_debug_type($value)];

            return null;
        }

        return $value;
    }
}
