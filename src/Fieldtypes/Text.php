<?php

namespace Statamic\Fieldtypes;

use Faker\Generator;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\SampleValue;
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
        return match ($this->sampleKind()) {
            'email' => 'name@example.com',
            'url' => 'https://example.com',
            'phone' => '555-0100',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'name' => 'Jane Doe',
            'company' => 'Acme Inc.',
            'address' => '123 Main Street',
            'city' => 'Springfield',
            'state' => 'Oregon',
            'country' => 'Canada',
            'postcode' => '97477',
            'job' => 'Product Manager',
            'subject' => 'A question about your services',
            'paragraph' => SampleValue::paragraph(),
            default => SampleValue::sentence(),
        };
    }

    public function fakeValue(Generator $faker): mixed
    {
        return (string) match ($this->sampleKind()) {
            'email' => $faker->safeEmail(),
            'url' => $faker->url(),
            'phone' => $faker->phoneNumber(),
            'first_name' => $faker->firstName(),
            'last_name' => $faker->lastName(),
            'name' => $faker->name(),
            'company' => $faker->company(),
            'address' => $faker->streetAddress(),
            'city' => $faker->city(),
            'state' => $faker->state(),
            'country' => $faker->country(),
            'postcode' => $faker->postcode(),
            'job' => $faker->jobTitle(),
            'subject' => $faker->sentence(6),
            'paragraph' => $faker->paragraph(2),
            default => $faker->sentence(4),
        };
    }

    private function sampleKind(): ?string
    {
        $inputType = match ($this->field()->get('input_type')) {
            'email' => 'email',
            'url' => 'url',
            'tel' => 'phone',
            default => null,
        };

        if ($inputType) {
            return $inputType;
        }

        $haystack = str_replace(' ', '_', Str::lower(implode(' ', array_filter(
            [$this->field()->handle(), $this->field()->display()],
            fn ($part) => is_string($part) && $part !== ''
        ))));

        $looksLike = fn (array $tokens) => collect($tokens)->contains(fn ($token) => str_contains($haystack, $token));

        return match (true) {
            $looksLike(['email', 'e-mail']) => 'email',
            $looksLike(['phone', 'tel', 'mobile', 'fax']) => 'phone',
            $looksLike(['first_name', 'firstname', 'given_name']) => 'first_name',
            $looksLike(['last_name', 'lastname', 'surname', 'family_name']) => 'last_name',
            $looksLike(['full_name', 'name']) || $this->field()->get('autocomplete') === 'name' => 'name',
            $looksLike(['company', 'organization', 'organisation', 'business']) => 'company',
            $looksLike(['address', 'street']) => 'address',
            $looksLike(['city', 'town']) => 'city',
            $looksLike(['state', 'province', 'region']) => 'state',
            $looksLike(['country']) => 'country',
            $looksLike(['zip', 'postal', 'postcode']) => 'postcode',
            $looksLike(['url', 'website', 'site']) => 'url',
            $looksLike(['job', 'title', 'position', 'role']) => 'job',
            $looksLike(['subject']) => 'subject',
            $looksLike(['message', 'comment', 'notes', 'description', 'bio']) => 'paragraph',
            default => null,
        };
    }
}
