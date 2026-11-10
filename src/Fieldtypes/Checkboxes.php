<?php

namespace Statamic\Fieldtypes;

use Faker\Generator;
use Statamic\Fields\Fieldtype;
use Statamic\Fieldtypes\Concerns\MigratesLegacyInlineConfig;
use Statamic\Forms\FieldOptions;

use function Statamic\trans as __;

class Checkboxes extends Fieldtype
{
    use HasSelectOptions {
        process as traitProcess;
    }
    use MigratesLegacyInlineConfig;

    protected $categories = ['controls'];
    protected $selectableInForms = true;
    protected $indexComponent = 'tags';

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Selection & Options'),
                'fields' => [
                    'options' => [
                        'display' => __('Options'),
                        'instructions' => __('statamic::fieldtypes.checkboxes.config.options'),
                        'type' => 'array',
                        'expand' => true,
                        'value_header' => __('Label').' ('.__('Optional').')',
                        'field' => [
                            'type' => 'text',
                        ],
                    ],
                ],
            ],
            [
                'display' => __('Appearance'),
                'fields' => [
                    'appearance' => [
                        'display' => __('Appearance'),
                        'instructions' => __('statamic::fieldtypes.checkboxes.config.appearance'),
                        'type' => 'control_appearance',
                        'default' => 'default',
                        'control' => 'checkbox',
                    ],
                ],
            ],
            [
                'display' => __('Data & Format'),
                'fields' => [
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'taggable',
                    ],
                ],
            ],
        ];
    }

    protected function multiple()
    {
        return true;
    }

    public function preProcessValidatable($value)
    {
        return collect($value)->filter()->values()->all();
    }

    public function process($data)
    {
        return collect($this->traitProcess($data))
            ->reject(fn ($value) => $value === null)
            ->values()
            ->all();
    }

    public function previewValue(): mixed
    {
        return array_slice(FieldOptions::keys($this->field()), 0, 1);
    }

    public function fakeValue(Generator $faker): mixed
    {
        return $this->randomOptionKeys($faker);
    }

    private function randomOptionKeys(Generator $faker): array
    {
        $keys = FieldOptions::keys($this->field());

        if ($keys === []) {
            return [];
        }

        return $faker->randomElements($keys, $faker->numberBetween(1, min(2, count($keys))));
    }
}
