<?php

namespace Statamic\Forms;

use Illuminate\Support\Str as IlluminateStr;
use Statamic\Contracts\Forms\Form as FormContract;
use Statamic\Fields\Field;
use Statamic\Forms\Fields\FormField;
use Statamic\Support\Str;

class FakeSubmissionGenerator
{
    protected $faker;

    public function __construct()
    {
        $this->faker = \Faker\Factory::create();
    }

    public function generate(FormContract $form): array
    {
        return $form->blueprint()->fields()->all()
            ->mapWithKeys(fn (Field $field) => [$field->handle() => $this->valueForField($field)])
            ->all();
    }

    private function valueForField(Field $field): mixed
    {
        $type = $field->type();
        $default = $field->defaultValue();

        if ($default !== null && ! in_array($type, [
            'toggle', 'checkboxes', 'select', 'radio', 'button_group', 'image_choice', 'yes_no', 'ranking', 'dictionary',
        ])) {
            return $default;
        }

        return match ($type) {
            'text', 'revealer', 'hidden' => $this->textValueFor($field),
            'slug' => IlluminateStr::slug($this->textValueFor($field)),
            'textarea', 'markdown' => $this->paragraph(),
            'integer', 'range' => $this->integerValueFor($field),
            'float' => $this->decimalNumber(),
            'toggle' => $this->boolean(),
            'date' => $this->date(),
            'time' => $this->time(),
            'date_and_time' => $this->dateTime(),
            'email' => $this->email(),
            'link', 'url' => $this->url(),
            'select', 'radio', 'button_group', 'yes_no' => $this->randomOptionKey($field),
            'image_choice' => $field->get('multiple')
                ? $this->randomOptionKeys($field)
                : $this->randomOptionKey($field),
            'checkboxes' => $this->randomOptionKeys($field),
            'opinion_scale' => $this->opinionScaleValueFor($field),
            'star_rating' => $this->starRatingValueFor($field),
            'ranking' => $this->rankingValueFor($field),
            'dictionary' => $this->dictionaryValueFor($field),
            'group' => $this->groupValueFor($field),
            'list' => [$this->word(), $this->word(), $this->word()],
            default => $field->fieldtype()->fakeValue() ?? $default,
        };
    }

    private function integerValueFor(Field $field): int
    {
        $min = (int) ($field->get('min') ?? 1);
        $max = (int) ($field->get('max') ?? 5000);

        return $this->number(min($min, $max), max($min, $max));
    }

    private function randomOptionKey(Field $field): mixed
    {
        $options = FieldOptions::keys($field);

        if (empty($options)) {
            return null;
        }

        return $options[array_rand($options)];
    }

    private function randomOptionKeys(Field $field): array
    {
        $options = FieldOptions::keys($field);

        if (empty($options)) {
            return [];
        }

        shuffle($options);

        return array_slice($options, 0, random_int(1, min(2, count($options))));
    }

    private function opinionScaleValueFor(Field $field): int
    {
        $min = (int) ($field->get('min') ?? 0);
        $max = (int) ($field->get('max') ?? 10);

        if ($max < $min) {
            [$min, $max] = [$max, $min];
        }

        return $this->number($min, $max);
    }

    private function starRatingValueFor(Field $field): int|float
    {
        $max = max(1, (int) ($field->get('max_stars') ?? 5));
        $allowHalf = (bool) $field->get('allow_half_stars');

        if (! $allowHalf) {
            return $this->number(1, $max);
        }

        $steps = (int) ($max / 0.5);
        $value = $this->number(1, $steps) * 0.5;

        return fmod($value, 1.0) === 0.0 ? (int) $value : $value;
    }

    private function rankingValueFor(Field $field): array
    {
        $options = FieldOptions::keys($field);

        shuffle($options);

        return array_values($options);
    }

    private function dictionaryValueFor(Field $field): mixed
    {
        try {
            $options = array_keys($field->fieldtype()->dictionary()->options());
        } catch (\Throwable) {
            return null;
        }

        if ($options === []) {
            return null;
        }

        $maxItems = $field->get('max_items');

        if ($maxItems === 1) {
            return $options[array_rand($options)];
        }

        shuffle($options);

        $count = $maxItems
            ? random_int(1, min((int) $maxItems, count($options)))
            : random_int(1, min(2, count($options)));

        return array_slice($options, 0, $count);
    }

    private function groupValueFor(Field $field): array
    {
        return collect($field->get('fields', []))
            ->filter(fn ($config) => is_array($config) && isset($config['handle'], $config['field']))
            ->mapWithKeys(function (array $config) {
                $nested = $this->resolveField($config['handle'], $config['field']);

                return [$nested->handle() => $this->valueForField($nested)];
            })
            ->all();
    }

    private function resolveField(string $handle, array $config): Field
    {
        return new Field($handle, (new FormField($handle, $config))->toFieldArray());
    }

    private function word(): string
    {
        return (string) $this->faker->word();
    }

    private function textValueFor(Field $field): string
    {
        return match ($field->get('input_type')) {
            'email' => $this->email(),
            'url' => $this->url(),
            'tel' => $this->phoneNumber(),
            default => $this->textValueFromContext($field),
        };
    }

    private function textValueFromContext(Field $field): string
    {
        if ($this->looksLike($field, ['email', 'e-mail'])) {
            return (string) $this->faker->safeEmail();
        }

        if ($this->looksLike($field, ['phone', 'tel', 'mobile', 'fax'])) {
            return (string) $this->faker->phoneNumber();
        }

        if ($this->looksLike($field, ['first_name', 'firstname', 'given_name'])) {
            return (string) $this->faker->firstName();
        }

        if ($this->looksLike($field, ['last_name', 'lastname', 'surname', 'family_name'])) {
            return (string) $this->faker->lastName();
        }

        if ($this->looksLike($field, ['full_name', 'name']) || $field->get('autocomplete') === 'name') {
            return (string) $this->faker->name();
        }

        if ($this->looksLike($field, ['company', 'organization', 'organisation', 'business'])) {
            return (string) $this->faker->company();
        }

        if ($this->looksLike($field, ['address', 'street'])) {
            return (string) $this->faker->streetAddress();
        }

        if ($this->looksLike($field, ['city', 'town'])) {
            return (string) $this->faker->city();
        }

        if ($this->looksLike($field, ['state', 'province', 'region'])) {
            return (string) $this->faker->state();
        }

        if ($this->looksLike($field, ['country'])) {
            return (string) $this->faker->country();
        }

        if ($this->looksLike($field, ['zip', 'postal', 'postcode'])) {
            return (string) $this->faker->postcode();
        }

        if ($this->looksLike($field, ['url', 'website', 'site'])) {
            return (string) $this->faker->url();
        }

        if ($this->looksLike($field, ['job', 'title', 'position', 'role'])) {
            return (string) $this->faker->jobTitle();
        }

        if ($this->looksLike($field, ['subject'])) {
            return (string) $this->faker->sentence(6);
        }

        if ($this->looksLike($field, ['message', 'comment', 'notes', 'description', 'bio'])) {
            return (string) $this->faker->paragraph(2);
        }

        return (string) $this->faker->sentence(4);
    }

    private function looksLike(Field $field, array $tokens): bool
    {
        $haystack = $this->normalizedFieldContext($field);

        foreach ($tokens as $token) {
            if (str_contains($haystack, Str::lower($token))) {
                return true;
            }
        }

        return false;
    }

    private function normalizedFieldContext(Field $field): string
    {
        $parts = [
            $field->handle(),
            $field->display(),
        ];

        return Str::lower(implode(' ', array_filter($parts, fn ($part) => is_string($part) && $part !== '')));
    }

    private function paragraph(): string
    {
        return (string) $this->faker->paragraph(2);
    }

    private function number(int $min, int $max): int
    {
        return (int) $this->faker->numberBetween($min, $max);
    }

    private function decimalNumber(): float
    {
        return (float) $this->faker->randomFloat(2, 1, 1000);
    }

    private function boolean(): bool
    {
        return (bool) $this->faker->boolean();
    }

    private function date(): string
    {
        return (string) $this->faker->date('Y-m-d');
    }

    private function time(): string
    {
        return (string) $this->faker->time('H:i');
    }

    private function dateTime(): string
    {
        return $this->faker->dateTime()->format(DATE_ATOM);
    }

    private function email(): string
    {
        return (string) $this->faker->safeEmail();
    }

    private function phoneNumber(): string
    {
        return (string) $this->faker->phoneNumber();
    }

    private function url(): string
    {
        return (string) $this->faker->url();
    }
}
