<?php

namespace Statamic\Forms;

use Faker\Generator;
use Statamic\Contracts\Forms\Form as FormContract;
use Statamic\Fields\Field;

class FakeSubmissionGenerator
{
    private const RANDOMIZED_DEFAULTS = [
        'toggle', 'checkboxes', 'select', 'radio', 'button_group', 'image_choice', 'yes_no', 'ranking', 'dictionary',
    ];

    protected Generator $faker;

    public static function enabled(): bool
    {
        return app()->isLocal() || app()->runningUnitTests();
    }

    public function __construct()
    {
        $this->faker = \Faker\Factory::create();
    }

    public function generate(FormContract $form): array
    {
        return $form->blueprint()->fields()->all()
            ->mapWithKeys(fn (Field $field) => [$field->handle() => static::valueFor($field, $this->faker)])
            ->all();
    }

    public static function valueFor(Field $field, Generator $faker): mixed
    {
        $default = $field->defaultValue();

        if ($default !== null && ! in_array($field->type(), self::RANDOMIZED_DEFAULTS)) {
            return $default;
        }

        return $field->fieldtype()->fakeValue($faker) ?? $default;
    }
}
