<?php

namespace Statamic\Forms\Fields;

use Illuminate\Support\Collection;
use Statamic\Facades;
use Statamic\Fields\Blueprint;
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
        return $this->items()->mapWithKeys(fn (array $field): array => [
            $field['handle'] => new FormField($field['handle'], $field['field']),
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
}
