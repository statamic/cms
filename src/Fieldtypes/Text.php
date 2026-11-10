<?php

namespace Statamic\Fieldtypes;

use Faker\Generator;
use Statamic\Fields\Fieldtype;
use Statamic\Forms\PreviewSubmission;
use Statamic\Support\Str;

use function Statamic\trans as __;

class Text extends Fieldtype
{
    protected $categories = ['text'];
    protected $selectableInForms = true;

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Input Behavior'),
                'fields' => [
                    'input_type' => [
                        'display' => __('Input Type'),
                        'instructions' => __('statamic::fieldtypes.text.config.input_type'),
                        'type' => 'select',
                        'default' => 'text',
                        'options' => [
                            'color',
                            'date',
                            'email',
                            'hidden',
                            'month',
                            'number',
                            'password',
                            'tel',
                            'text',
                            'time',
                            'url',
                            'week',
                        ],
                        'width' => '50',
                    ],
                    'character_limit' => [
                        'display' => __('Character Limit'),
                        'instructions' => __('statamic::fieldtypes.text.config.character_limit'),
                        'type' => 'integer',
                        'width' => '50',
                    ],
                    'autocomplete' => [
                        'display' => __('Autocomplete'),
                        'instructions' => __('statamic::fieldtypes.text.config.autocomplete'),
                        'type' => 'select',
                        'clearable' => true,
                        'options' => [
                            'additional-name',
                            'address-level1',
                            'address-level2',
                            'address-level3',
                            'address-level4',
                            'address-line1',
                            'address-line2',
                            'address-line3',
                            'bday',
                            'bday-day',
                            'bday-month',
                            'bday-year',
                            'cc-additional-name',
                            'cc-csc',
                            'cc-exp',
                            'cc-exp-month',
                            'cc-exp-year',
                            'cc-family-name',
                            'cc-given-name',
                            'cc-name',
                            'cc-number',
                            'cc-type',
                            'country',
                            'country-name',
                            'current-password',
                            'email',
                            'family-name',
                            'given-name',
                            'honorific-prefix',
                            'honorific-suffix',
                            'language',
                            'name',
                            'new-password',
                            'nickname',
                            'off',
                            'on',
                            'organization',
                            'organization-title',
                            'photo',
                            'postal-code',
                            'sex',
                            'street-address',
                            'tel',
                            'tel-area-code',
                            'tel-country-code',
                            'tel-extension',
                            'tel-local',
                            'tel-local-prefix',
                            'tel-local-suffix',
                            'tel-national',
                            'transaction-amount',
                            'transaction-currency',
                            'url',
                            'username',
                        ],
                        'width' => '50',
                    ],
                ],
            ],
            [
                'display' => __('Appearance'),
                'fields' => [
                    'placeholder' => [
                        'display' => __('Placeholder'),
                        'instructions' => __('statamic::fieldtypes.text.config.placeholder'),
                        'type' => 'text',
                        'width' => '100',
                    ],
                    'prepend' => [
                        'display' => __('Prepend'),
                        'instructions' => __('statamic::fieldtypes.text.config.prepend'),
                        'type' => 'text',
                        'width' => '50',
                    ],
                    'append' => [
                        'display' => __('Append'),
                        'instructions' => __('statamic::fieldtypes.text.config.append'),
                        'type' => 'text',
                        'width' => '50',
                    ],
                ],
            ],
            [
                'display' => __('Data & Format'),
                'fields' => [
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                        'width' => '50',
                    ],
                ],
            ],
            [
                'display' => __('Advanced'),
                'fields' => [
                    'antlers' => [
                        'display' => __('Allow Antlers'),
                        'instructions' => __('statamic::fieldtypes.any.config.antlers'),
                        'type' => 'toggle',
                    ],
                ],
            ],
        ];
    }

    public function process($data)
    {
        if ($data !== null && $this->config('input_type') === 'number') {
            return Str::contains($data, '.') ? (float) $data : (int) $data;
        }

        return $data;
    }

    public function preProcessIndex($value)
    {
        if (is_null($value)) {
            return null;
        }

        return $this->config('prepend').$value.$this->config('append');
    }

    public function previewValue(): mixed
    {
        return match ($this->field()->get('input_type')) {
            'email' => 'name@example.com',
            'url' => 'https://example.com',
            default => PreviewSubmission::placeholder($this->field()),
        };
    }

    public function fakeValue(Generator $faker): mixed
    {
        return match ($this->field()->get('input_type')) {
            'email' => $faker->safeEmail(),
            'url' => $faker->url(),
            'tel' => $faker->phoneNumber(),
            default => $this->fakeValueFromContext($faker),
        };
    }

    private function fakeValueFromContext(Generator $faker): string
    {
        $haystack = Str::lower(implode(' ', array_filter(
            [$this->field()->handle(), $this->field()->display()],
            fn ($part) => is_string($part) && $part !== ''
        )));

        $looksLike = fn (array $tokens) => collect($tokens)->contains(fn ($token) => str_contains($haystack, $token));

        return (string) match (true) {
            $looksLike(['email', 'e-mail']) => $faker->safeEmail(),
            $looksLike(['phone', 'tel', 'mobile', 'fax']) => $faker->phoneNumber(),
            $looksLike(['first_name', 'firstname', 'given_name']) => $faker->firstName(),
            $looksLike(['last_name', 'lastname', 'surname', 'family_name']) => $faker->lastName(),
            $looksLike(['full_name', 'name']) || $this->field()->get('autocomplete') === 'name' => $faker->name(),
            $looksLike(['company', 'organization', 'organisation', 'business']) => $faker->company(),
            $looksLike(['address', 'street']) => $faker->streetAddress(),
            $looksLike(['city', 'town']) => $faker->city(),
            $looksLike(['state', 'province', 'region']) => $faker->state(),
            $looksLike(['country']) => $faker->country(),
            $looksLike(['zip', 'postal', 'postcode']) => $faker->postcode(),
            $looksLike(['url', 'website', 'site']) => $faker->url(),
            $looksLike(['job', 'title', 'position', 'role']) => $faker->jobTitle(),
            $looksLike(['subject']) => $faker->sentence(6),
            $looksLike(['message', 'comment', 'notes', 'description', 'bio']) => $faker->paragraph(2),
            default => $faker->sentence(4),
        };
    }
}
