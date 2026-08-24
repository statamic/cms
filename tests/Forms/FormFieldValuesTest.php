<?php

namespace Tests\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Forms\FormFieldValues;
use Tests\Factories\EntryFactory;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FormFieldValuesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function makeEntry(array $data)
    {
        Blueprint::make('event')->setNamespace('collections.events')->setContents(['fields' => [
            ['handle' => 'title', 'field' => ['type' => 'text']],
            ['handle' => 'top_form', 'field' => ['type' => 'form', 'max_items' => 1]],
            ['handle' => 'other_forms', 'field' => ['type' => 'form', 'max_items' => 3]],
            ['handle' => 'details', 'field' => ['type' => 'group', 'fields' => [
                ['handle' => 'group_form', 'field' => ['type' => 'form', 'max_items' => 1]],
            ]]],
            ['handle' => 'schedule', 'field' => ['type' => 'grid', 'fields' => [
                ['handle' => 'grid_form', 'field' => ['type' => 'form', 'max_items' => 1]],
            ]]],
            ['handle' => 'blocks', 'field' => ['type' => 'replicator', 'sets' => [
                'rsvp' => ['fields' => [
                    ['handle' => 'replicator_form', 'field' => ['type' => 'form', 'max_items' => 1]],
                    ['handle' => 'inner', 'field' => ['type' => 'group', 'fields' => [
                        ['handle' => 'inner_form', 'field' => ['type' => 'form', 'max_items' => 1]],
                    ]]],
                ]],
            ]]],
            ['handle' => 'content', 'field' => ['type' => 'bard', 'sets' => [
                'rsvp' => ['fields' => [
                    ['handle' => 'bard_form', 'field' => ['type' => 'form', 'max_items' => 1]],
                ]],
            ]]],
        ]])->save();

        return (new EntryFactory)->collection('events')->id('event-1')->slug('event-one')->data($data)->create();
    }

    #[Test]
    public function it_finds_form_values_at_every_level()
    {
        $entry = $this->makeEntry([
            'top_form' => ['form' => 'contact', 'config' => ['submission_limit' => 1]],
            'other_forms' => ['newsletter', 'feedback'],
            'details' => ['group_form' => 'group_contact'],
            'schedule' => [
                ['grid_form' => 'grid_contact'],
            ],
            'blocks' => [
                ['type' => 'rsvp', 'replicator_form' => 'replicator_contact', 'inner' => ['inner_form' => 'inner_contact']],
                ['type' => 'unknown_set', 'replicator_form' => 'ignored'],
            ],
            'content' => [
                ['type' => 'paragraph', 'content' => []],
                ['type' => 'set', 'attrs' => ['id' => '1', 'values' => ['type' => 'rsvp', 'bard_form' => 'bard_contact']]],
            ],
        ]);

        $this->assertEquals([
            ['form' => 'contact', 'config' => ['submission_limit' => 1]],
            ['newsletter', 'feedback'],
            'group_contact',
            'grid_contact',
            'replicator_contact',
            'inner_contact',
            'bard_contact',
        ], FormFieldValues::on($entry)->all()->values()->all());
    }

    #[Test]
    public function it_finds_values_referencing_a_form()
    {
        $entry = $this->makeEntry([
            'top_form' => ['form' => 'contact', 'config' => ['submission_limit' => 1]],
            'other_forms' => ['newsletter', 'contact'],
            'blocks' => [
                ['type' => 'rsvp', 'replicator_form' => 'feedback'],
            ],
        ]);

        $this->assertCount(2, FormFieldValues::on($entry)->referencing('contact'));
        $this->assertCount(1, FormFieldValues::on($entry)->referencing('feedback'));
        $this->assertCount(0, FormFieldValues::on($entry)->referencing('missing'));
    }

    #[Test]
    public function it_ignores_fields_without_values()
    {
        $entry = $this->makeEntry(['title' => 'Event One']);

        $this->assertEquals([], FormFieldValues::on($entry)->all()->all());
    }
}
