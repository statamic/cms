<?php

namespace Statamic\Forms\Fields;

use Facades\Statamic\Forms\Fields\FormFieldRepository;
use Illuminate\Support\Collection;
use Statamic\Facades;
use Statamic\Fields\Blueprint;
use Statamic\Support\Arr;

class FormFields
{
    // todo: separate FormField and FormFieldtype
    // todo: write tests
    // todo: cache some things?

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
        return $this->items()->mapWithKeys(fn (array $field): array => [
            $field['handle'] => FormFieldRepository::find($field['field']['type'])->setConfig(Arr::except($field['field'], 'type')),
        ]);
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
                        ->map(function (array $field): array {
                            $formField = $this->field($field['handle']);

                            return [
                                'handle' => $field['handle'],
                                'field' => $formField->toFieldArray(),
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
}
