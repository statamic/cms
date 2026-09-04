<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Email;
use Tests\TestCase;

class EmailTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new Email)->setField(new FormField('email', ['type' => 'email']));

        $this->assertEquals([
            'type' => 'text',
            'input_type' => 'email',
            'placeholder' => null,
            'validate' => ['email'],
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_preserves_existing_validation_rules()
    {
        $fieldtype = (new Email)->setField(new FormField('email', [
            'type' => 'email',
            'validate' => ['required', 'max:255'],
        ]));

        $this->assertEquals([
            'type' => 'text',
            'input_type' => 'email',
            'placeholder' => null,
            'validate' => ['required', 'max:255', 'email'],
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_does_not_duplicate_the_email_validation_rule()
    {
        $fieldtype = (new Email)->setField(new FormField('email', [
            'type' => 'email',
            'validate' => ['required', 'email'],
        ]));

        $this->assertEquals([
            'type' => 'text',
            'input_type' => 'email',
            'placeholder' => null,
            'validate' => ['required', 'email'],
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new Email)->setField(new FormField('email', [
            'type' => 'email',
            'append' => '@example.com',
        ]));

        $this->assertEquals([
            'type' => 'text',
            'input_type' => 'email',
            'placeholder' => null,
            'validate' => ['email'],
            'append' => '@example.com',
        ], $fieldtype->toFieldArray());
    }
}
