<?php

namespace Statamic\Forms;

use Faker\Generator;
use Statamic\Contracts\Forms\Form as FormContract;
use Statamic\Fields\Field;
use Statamic\Fields\SampleValue;

class FakeSubmissionGenerator
{
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
            ->mapWithKeys(fn (Field $field) => [$field->handle() => SampleValue::fake($field, $this->faker)])
            ->all();
    }
}
