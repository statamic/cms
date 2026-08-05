<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Name;
use Tests\TestCase;

class NameTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new Name)->setField(new FormField('name', [
            'type' => 'name',
            'placeholder' => 'Your name',
        ]));

        $this->assertEquals([
            'type' => 'text',
            'autocomplete' => 'name',
            'placeholder' => 'Your name',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new Name)->setField(new FormField('name', [
            'type' => 'name',
            'placeholder' => 'Your name',
            'default' => 'John',
        ]));

        $this->assertEquals([
            'type' => 'text',
            'autocomplete' => 'name',
            'placeholder' => 'Your name',
            'default' => 'John',
        ], $fieldtype->toFieldArray());
    }
}
