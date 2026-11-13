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
        return $this->sampleKindFromInputType()
            ?? $this->sampleKindFromAutocomplete()
            ?? $this->sampleKindFromWords();
    }

    private function sampleKindFromInputType(): ?string
    {
        return match ($this->field()->get('input_type')) {
            'email' => 'email',
            'url' => 'url',
            'tel' => 'phone',
            default => null,
        };
    }

    private function sampleKindFromAutocomplete(): ?string
    {
        $autocomplete = $this->field()->get('autocomplete');

        if (! is_string($autocomplete) || $autocomplete === '') {
            return null;
        }

        return match (Str::afterLast(Str::lower(trim($autocomplete)), ' ')) {
            'email' => 'email',
            'url' => 'url',
            'tel', 'tel-national', 'tel-local' => 'phone',
            'given-name' => 'first_name',
            'family-name' => 'last_name',
            'name' => 'name',
            'organization' => 'company',
            'organization-title' => 'job',
            'street-address', 'address-line1', 'address-line2', 'address-line3' => 'address',
            'address-level2' => 'city',
            'address-level1' => 'state',
            'country', 'country-name' => 'country',
            'postal-code' => 'postcode',
            default => null,
        };
    }

    private function sampleKindFromWords(): ?string
    {
        // Checked in order, so "company_email" is an email, "company_name" is a company and "city_name" is a city.
        $kinds = [
            'first_name' => [['first', 'name'], ['given', 'name'], ['firstname'], ['forename']],
            'last_name' => [['last', 'name'], ['family', 'name'], ['lastname'], ['surname']],
            'email' => [['email'], ['e', 'mail']],
            'phone' => [['phone'], ['telephone'], ['tel'], ['mobile'], ['cell'], ['fax']],
            'url' => [['url'], ['website'], ['homepage']],
            'postcode' => [['postcode'], ['postal', 'code'], ['post', 'code'], ['zip'], ['zipcode']],
            'company' => [['company'], ['organization'], ['organisation'], ['business'], ['employer']],
            'address' => [['address'], ['street']],
            'city' => [['city'], ['town']],
            'state' => [['state'], ['province'], ['region'], ['county']],
            'country' => [['country']],
            'job' => [['job'], ['occupation'], ['position']],
            'subject' => [['subject']],
            'paragraph' => [['message'], ['comment'], ['comments'], ['notes'], ['description'], ['bio']],
            'name' => [['name'], ['fullname']],
        ];

        $wordLists = collect([$this->field()->handle(), $this->field()->display()])
            ->filter(fn ($part) => is_string($part) && $part !== '')
            ->map(fn (string $part) => $this->sampleWords($part));

        foreach ($kinds as $kind => $sequences) {
            foreach ($sequences as $sequence) {
                if ($wordLists->contains(fn (array $words) => $this->containsSequence($words, $sequence))) {
                    return $kind;
                }
            }
        }

        return null;
    }

    private function sampleWords(string $text): array
    {
        $text = preg_replace('/(?<=\p{Ll})(?=\p{Lu})/u', ' ', $text);

        return preg_split('/[^\p{L}\p{N}]+/u', Str::lower($text), -1, PREG_SPLIT_NO_EMPTY);
    }

    private function containsSequence(array $words, array $sequence): bool
    {
        $length = count($sequence);

        for ($i = 0; $i <= count($words) - $length; $i++) {
            if (array_slice($words, $i, $length) === $sequence) {
                return true;
            }
        }

        return false;
    }
}
