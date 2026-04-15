<?php

namespace Statamic\Forms\Fields;

use Facades\Statamic\Fields\FieldRepository;
use Illuminate\Support\Collection;
use Statamic\Exceptions\FieldsetNotFoundException;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\Fieldset as FieldsetRepository;
use Statamic\Fields\Blueprint;
use Statamic\Fields\FieldsetRecursionStack;
use Statamic\Support\Arr;

class FormFields
{
    public function __construct(protected array $contents)
    {
    }

    public function contents(): array
    {
        return $this->contents;
    }

    public function items(): Collection
    {
        return collect($this->contents['sections'] ?? [])->flatMap(fn (array $section): array => $section['fields'] ?? []);
    }

    public function fields(): Collection
    {
        return $this->items()->flatMap(function (array $config): array {
            if (isset($config['import'])) {
                return $this->getImportedFields($config);
            }

            if (is_string($config['field'])) {
                return [$config['handle'] => $this->getReferencedField($config)];
            }

            return [$config['handle'] => new FormField($config['handle'], $config['field'])];
        })->filter();
    }

    public function field(string $handle): ?FormField
    {
        return $this->fields()->get($handle);
    }

    public function toBlueprint(): Blueprint
    {
        $contents = collect($this->contents['sections'] ?? [])
            ->map(function (array $section): array {
                return [
                    ...$section,
                    'fields' => collect($section['fields'] ?? [])
                        ->map(function (array $config): array {
                            if (isset($config['import']) || is_string($config['field'])) {
                                return $config;
                            }

                            $formField = $this->field($config['handle']);

                            return [
                                'handle' => $config['handle'],
                                'field' => Arr::removeNullValues($formField->toFieldArray()),
                            ];
                        })
                        ->all(),
                ];
            })
            ->all();

        return Facades\Blueprint::make()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => $contents,
                ],
            ],
        ]);
    }

    /**
     * Borrowed from \Statamic\Fields\Fields.
     */
    private function getReferencedField(array $config): FormField
    {
        if (! $field = FieldRepository::find($config['field'])) {
            throw new \Exception("Field {$config['field']} not found.");
        }

        if ($overrides = Arr::get($config, 'config')) {
            $field->setConfig(array_merge($field->config(), $overrides));
        }

        return new FormField($field->handle(), $field->config());
    }

    /**
     * Borrowed from \Statamic\Fields\Fields.
     */
    private function getImportedFields(array $config): array
    {
        $recursion = tap(app(FieldsetRecursionStack::class))->push($config['import']);

        $blink = 'form-fields-imported-fields-'.md5(json_encode($config));

        $imported = Blink::once($blink, function () use ($config) {
            if (! $fieldset = FieldsetRepository::find($config['import'])) {
                throw new FieldsetNotFoundException($config['import']);
            }

            $fields = $fieldset->fields()->all();

            if ($overrides = $config['config'] ?? null) {
                $fields = $fields->map(function ($field, $handle) use ($overrides) {
                    return $field->setConfig(array_merge($field->config(), $overrides[$handle] ?? []));
                });
            }

            if ($prefix = Arr::get($config, 'prefix')) {
                $fields = $fields->mapWithKeys(function ($field) use ($prefix) {
                    $field = clone $field;
                    $handle = $prefix.$field->handle();
                    $prefix = $prefix.$field->prefix();

                    return [$handle => $field->setHandle($handle)->setPrefix($prefix)];
                });
            }

            return $fields;
        })->map(function ($field) {
            return new FormField($field->handle(), $field->config());
        })->all();

        $recursion->pop();

        return $imported;
    }
}
