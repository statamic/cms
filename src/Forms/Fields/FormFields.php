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
use Statamic\Statamic;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class FormFields
{
    public function __construct(protected array $contents)
    {
        if (isset($this->contents['pages']) && ! Statamic::formsProInstalled()) {
            $this->contents = [
                'sections' => collect($this->contents['pages'])
                    ->flatMap(fn (array $page): array => $page['sections'] ?? [])
                    ->all(),
            ];
        }
    }

    public function contents(): array
    {
        return $this->contents;
    }

    public function pages(): Collection
    {
        $pages = isset($this->contents['pages'])
            ? collect($this->contents['pages'])
            : collect([['sections' => $this->contents['sections'] ?? []]]);

        return $pages->map(fn (array $page, int $index): array => [
            ...$page,
            'id' => $page['id'] ?? ($pages->count() === 1 ? 'main' : 'page_'.($index + 1)),
        ]);
    }

    public function sections(): Collection
    {
        return $this->pages()->flatMap(fn (array $page): array => $page['sections'] ?? []);
    }

    public function items(): Collection
    {
        return $this->sections()->flatMap(fn (array $section): array => $section['fields'] ?? []);
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
        $tabs = $this->pages()
            ->mapWithKeys(function (array $page, int $index): array {
                $sections = collect($page['sections'] ?? [])
                    ->map(function (array $section): array {
                        return [
                            ...$section,
                            'fields' => collect($section['fields'] ?? [])
                                ->reject(fn (array $config): bool => $config['field']['hidden'] ?? false)
                                ->values()
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

                return [
                    $page['id'] => [
                        ...Arr::except($page, 'id'),
                        'display' => $page['display'] ?? __('Page :current of :total', ['current' => $index + 1, 'total' => $this->pages()->count()]),
                        'sections' => $sections,
                    ],
                ];
            })
            ->all();

        return Facades\Blueprint::make()->setContents(['tabs' => $tabs]);
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
