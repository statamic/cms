<?php

namespace Tests\Fieldtypes;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Rules\UniqueEntryValue;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function groupField(array $validate): Field
    {
        return new Field('details', [
            'type' => 'group',
            'fields' => [
                ['handle' => 'code', 'field' => ['type' => 'text', 'validate' => $validate]],
            ],
        ]);
    }

    #[Test]
    public function it_adds_entry_validation_replacements_to_nested_string_rules()
    {
        $entry = EntryFactory::id('123')->collection('blog')->slug('hello')->locale('en')->create();

        $field = $this->groupField(['in:{collection},{id},{site}'])->setParent($entry)->setValue(['code' => 'alpha']);

        $rules = $field->fieldtype()->extraRules();

        $this->assertContains('in:blog,123,en', $rules['details.code']);
    }

    #[Test]
    public function it_adds_entry_validation_replacements_to_nested_class_based_rules()
    {
        $entry = EntryFactory::id('123')->collection('blog')->slug('hello')->locale('en')->create();

        $field = $this->groupField(['new \Statamic\Rules\UniqueEntryValue({collection}, {id}, {site})'])->setParent($entry)->setValue(['code' => 'alpha']);

        $rules = collect($field->fieldtype()->extraRules()['details.code'])->whereInstanceOf(UniqueEntryValue::class);

        $this->assertCount(1, $rules);
        $this->assertEquals(new UniqueEntryValue('blog', '123', 'en'), $rules->first());
    }

    #[Test]
    public function it_adds_entry_validation_replacements_when_nested_inside_a_replicator()
    {
        $entry = EntryFactory::id('123')->collection('blog')->slug('hello')->locale('en')->create();

        $field = (new Field('blocks', [
            'type' => 'replicator',
            'sets' => [
                'main' => [
                    'sets' => [
                        'one' => [
                            'fields' => [
                                ['handle' => 'details', 'field' => ['type' => 'group', 'fields' => [
                                    ['handle' => 'code', 'field' => ['type' => 'text', 'validate' => ['in:{collection},{id},{site}']]],
                                ]]],
                            ],
                        ],
                    ],
                ],
            ],
        ]))->setParent($entry)->setValue([['type' => 'one', 'details' => ['code' => 'alpha']]]);

        $rules = $field->fieldtype()->extraRules();

        $this->assertContains('in:blog,123,en', $rules['blocks.0.details.code']);
    }

    #[Test]
    public function it_does_not_add_entry_validation_replacements_when_the_parent_is_not_an_entry()
    {
        $field = $this->groupField(['in:{collection},{id},{site}'])->setValue(['code' => 'alpha']);

        $rules = $field->fieldtype()->extraRules();

        $this->assertContains('in:NULL,NULL,NULL', $rules['details.code']);
    }
}
