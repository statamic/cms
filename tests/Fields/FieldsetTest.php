<?php

namespace Tests\Fields;

use Facades\Statamic\Fields\FieldsetRepository;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldset;
use Tests\TestCase;

class FieldsetTest extends TestCase
{
    /** @test */
    public function it_gets_the_handle()
    {
        $fieldset = new Fieldset;
        $this->assertNull($fieldset->handle());

        $return = $fieldset->setHandle('test');

        $this->assertEquals($fieldset, $return);
        $this->assertEquals('test', $fieldset->handle());
    }

    /** @test */
    public function it_gets_contents()
    {
        $fieldset = new Fieldset;
        $this->assertEquals([], $fieldset->contents());

        $contents = [
            'fields' => [
                ['handle' => 'one', 'field' => ['type' => 'text']],
            ],
        ];

        $return = $fieldset->setContents($contents);

        $this->assertEquals($fieldset, $return);
        $this->assertEquals($contents, $fieldset->contents());
    }

    /** @test */
    public function it_gets_the_title()
    {
        $fieldset = (new Fieldset)->setContents([
            'title' => 'Test',
        ]);

        $this->assertEquals('Test', $fieldset->title());
    }

    /** @test */
    public function the_title_falls_back_to_a_humanized_handle()
    {
        $fieldset = (new Fieldset)->setHandle('the_blueprint_handle');

        $this->assertEquals('The blueprint handle', $fieldset->title());
    }

    /** @test */
    public function it_gets_fields()
    {
        $fieldset = new Fieldset;

        $fieldset->setContents([
            'fields' => [
                [
                    'handle' => 'one',
                    'field' => ['type' => 'text'],
                ],
                [
                    'handle' => 'two',
                    'field' => ['type' => 'textarea'],
                ],
            ],
        ]);

        $fields = $fieldset->fields();

        $this->assertInstanceOf(Fields::class, $fields);
        $this->assertEveryItemIsInstanceOf(Field::class, $fields = $fields->all());
        $this->assertEquals(['one', 'two'], $fields->map->handle()->values()->all());
        $this->assertEquals(['text', 'textarea'], $fields->map->type()->values()->all());
    }

    /** @test */
    public function it_gets_fields_using_legacy_syntax()
    {
        $fieldset = new Fieldset;

        $fieldset->setContents([
            'fields' => [
                'one' => [
                    'type' => 'text',
                ],
                'two' => [
                    'type' => 'textarea',
                ],
            ],
        ]);

        $fields = $fieldset->fields();

        $this->assertInstanceOf(Fields::class, $fields);
        $this->assertEveryItemIsInstanceOf(Field::class, $fields = $fields->all());
        $this->assertEquals(['one', 'two'], $fields->map->handle()->values()->all());
        $this->assertEquals(['text', 'textarea'], $fields->map->type()->values()->all());
    }

    /** @test */
    public function gets_a_single_field()
    {
        $fieldset = new Fieldset;

        $fieldset->setContents([
            'fields' => [
                [
                    'handle' => 'one',
                    'field' => [
                        'type' => 'textarea',
                        'display' => 'First field',
                    ],
                ],
            ],
        ]);

        $field = $fieldset->field('one');

        $this->assertInstanceOf(Field::class, $field);
        $this->assertEquals('First field', $field->display());
        $this->assertEquals('textarea', $field->type());

        $this->assertNull($fieldset->field('unknown'));
    }

    /** @test */
    public function it_saves_through_the_repository()
    {
        FieldsetRepository::shouldReceive('save')->with($fieldset = new Fieldset)->once();

        $return = $fieldset->save();

        $this->assertEquals($fieldset, $return);
    }
}
