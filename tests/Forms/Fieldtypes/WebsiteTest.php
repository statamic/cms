<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Website;
use Tests\TestCase;

class WebsiteTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new Website)->setField(new FormField('website', [
            'type' => 'website',
            'placeholder' => 'https://example.com',
        ]));

        $this->assertEquals([
            'type' => 'text',
            'input_type' => 'url',
            'placeholder' => 'https://example.com',
            'validate' => ['url'],
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_does_not_duplicate_url_validation_rule()
    {
        $fieldtype = (new Website)->setField(new FormField('website', [
            'type' => 'website',
            'placeholder' => 'https://example.com',
            'validate' => ['url'],
        ]));

        $this->assertEquals([
            'type' => 'text',
            'input_type' => 'url',
            'placeholder' => 'https://example.com',
            'validate' => ['url'],
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_preserves_existing_validation_rules()
    {
        $fieldtype = (new Website)->setField(new FormField('website', [
            'type' => 'website',
            'placeholder' => 'https://example.com',
            'validate' => ['required'],
        ]));

        $this->assertEquals([
            'type' => 'text',
            'input_type' => 'url',
            'placeholder' => 'https://example.com',
            'validate' => ['required', 'url'],
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new Website)->setField(new FormField('website', [
            'type' => 'website',
            'placeholder' => 'https://example.com',
            'default' => 'https://statamic.com',
        ]));

        $this->assertEquals([
            'type' => 'text',
            'input_type' => 'url',
            'placeholder' => 'https://example.com',
            'validate' => ['url'],
            'default' => 'https://statamic.com',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_does_not_add_the_plain_url_rule_when_a_parameterized_one_exists()
    {
        $fieldtype = (new Website)->setField(new FormField('website', [
            'type' => 'website',
            'validate' => ['required', 'url:https'],
        ]));

        $this->assertEquals([
            'type' => 'text',
            'input_type' => 'url',
            'placeholder' => null,
            'validate' => ['required', 'url:https'],
        ], $fieldtype->toFieldArray());
    }
}
