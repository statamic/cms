<?php

namespace Tests\Forms\Fieldtypes;

use Facades\Statamic\Fields\FieldRepository;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Group;
use Tests\TestCase;

class GroupTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new Group)->setField(new FormField('my_group', [
            'type' => 'group',
            'display' => 'My Group',
            'fields' => [
                ['handle' => 'name', 'field' => ['type' => 'text']],
            ],
        ]));

        $this->assertEquals([
            'type' => 'group',
            'display' => 'My Group',
            'fields' => [
                ['handle' => 'name', 'field' => ['type' => 'text']],
            ],
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_uses_the_form_group_fields_fieldtype_for_its_children()
    {
        $this->assertEquals('form_group_fields', (new Group)->configFields()->get('fields')->type());
    }

    #[Test]
    public function it_converts_form_fieldtype_children_into_core_fields()
    {
        $fieldtype = (new Group)->setField(new FormField('my_group', [
            'type' => 'group',
            'fields' => [
                ['handle' => 'name', 'field' => ['type' => 'short_answer', 'display' => 'Name', 'placeholder' => 'Jane']],
                ['handle' => 'bio', 'field' => ['type' => 'long_answer']],
            ],
        ]));

        $this->assertEquals([
            ['handle' => 'name', 'field' => ['type' => 'text', 'display' => 'Name', 'placeholder' => 'Jane']],
            ['handle' => 'bio', 'field' => ['type' => 'textarea']],
        ], $fieldtype->toFieldArray()['fields']);
    }

    #[Test]
    #[DataProvider('sharedHandleProvider')]
    public function it_converts_children_whose_handle_is_both_a_form_and_core_fieldtype(array $config, array $expected)
    {
        $fieldtype = (new Group)->setField(new FormField('my_group', [
            'type' => 'group',
            'fields' => [['handle' => 'child', 'field' => $config]],
        ]));

        $this->assertEquals(
            [['handle' => 'child', 'field' => $expected]],
            $fieldtype->toFieldArray()['fields']
        );
    }

    public static function sharedHandleProvider(): array
    {
        return [
            'toggle' => [
                ['type' => 'toggle', 'inline_label' => 'Yes please'],
                ['type' => 'toggle', 'inline_label' => 'Yes please'],
            ],
            'checkboxes (form options)' => [
                ['type' => 'checkboxes', 'options' => [
                    ['key' => 'a', 'value' => 'Apple'],
                    ['key' => 'b', 'value' => 'Banana', 'hidden' => true],
                ]],
                ['type' => 'checkboxes', 'options' => [
                    ['key' => 'a', 'value' => 'Apple'],
                    ['key' => 'b', 'value' => 'Banana', 'hidden' => true],
                ], 'validate' => ['array', 'in:"a"']],
            ],
            'checkboxes (core options)' => [
                ['type' => 'checkboxes', 'options' => ['a' => 'Apple', 'b' => 'Banana']],
                ['type' => 'checkboxes', 'options' => ['a' => 'Apple', 'b' => 'Banana'], 'validate' => ['array', 'in:"a","b"']],
            ],
            'dictionary' => [
                ['type' => 'dictionary', 'dictionary' => 'countries'],
                ['type' => 'dictionary', 'dictionary' => 'countries'],
            ],
        ];
    }

    #[Test]
    public function it_passes_imported_and_referenced_children_through()
    {
        $fieldtype = (new Group)->setField(new FormField('my_group', [
            'type' => 'group',
            'fields' => [
                ['import' => 'address'],
                ['handle' => 'city', 'field' => 'address.city'],
            ],
        ]));

        $this->assertEquals([
            ['import' => 'address'],
            ['handle' => 'city', 'field' => 'address.city'],
        ], $fieldtype->toFieldArray()['fields']);
    }

    #[Test]
    #[DataProvider('uploadChildProvider')]
    public function it_leaves_out_upload_children_and_logs_a_warning(string $type)
    {
        Log::shouldReceive('warning')->once()->withArgs(function ($message) {
            return str_contains($message, 'my_group')
                && str_contains($message, 'resume');
        });

        $fieldtype = (new Group)->setField(new FormField('my_group', [
            'type' => 'group',
            'fields' => [
                ['handle' => 'name', 'field' => ['type' => 'text']],
                ['handle' => 'resume', 'field' => ['type' => $type, 'container' => 'assets']],
            ],
        ]));

        $this->assertEquals([
            ['handle' => 'name', 'field' => ['type' => 'text']],
        ], $fieldtype->toFieldArray()['fields']);

        $fieldtype->toFieldArray();
    }

    #[Test]
    public function it_leaves_out_referenced_upload_children()
    {
        FieldRepository::shouldReceive('find')->with('uploads.resume')->andReturn(new Field('resume', ['type' => 'files']));
        FieldRepository::shouldReceive('find')->with('address.city')->andReturn(new Field('city', ['type' => 'text']));
        Log::shouldReceive('warning')->once();

        $fieldtype = (new Group)->setField(new FormField('my_group', [
            'type' => 'group',
            'fields' => [
                ['handle' => 'resume', 'field' => 'uploads.resume'],
                ['handle' => 'city', 'field' => 'address.city'],
            ],
        ]));

        $this->assertEquals([
            ['handle' => 'city', 'field' => 'address.city'],
        ], $fieldtype->toFieldArray()['fields']);
    }

    public static function uploadChildProvider(): array
    {
        return [
            'assets' => ['assets'],
            'files' => ['files'],
            'upload' => ['upload'],
            'form_upload' => ['form_upload'],
        ];
    }
}
