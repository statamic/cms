<?php

namespace Tests\UpdateScripts;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\UpdateScripts\ConvertDatesToUtc;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;
use Tests\UpdateScripts\Concerns\RunsUpdateScripts;

class ConvertDatesToUtcTest extends TestCase
{
    use PreventSavingStacheItemsToDisk, RunsUpdateScripts;

    #[Test]
    public function it_is_registered()
    {
        $this->assertUpdateScriptRegistered(ConvertDatesToUtc::class);
    }

    #[Test]
    public function is_skipped_when_application_timezone_is_utc()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function it_can_convert_date_fields_in_entries()
    {
        config()->set('app.timezone', 'America/New_York'); // -05:00
        date_default_timezone_set('America/New_York');

        $collection = tap(Collection::make('articles')->dated(true))->save();

        $collection->entryBlueprint()->setContents([
            'fields' => [
                ['handle' => 'date', 'field' => ['type' => 'date', 'time_enabled' => true]],
                ['handle' => 'date_with_time', 'field' => ['type' => 'date', 'time_enabled' => true]],
                ['handle' => 'date_with_time_and_seconds', 'field' => ['type' => 'date', 'time_enabled' => true, 'time_seconds_enabled' => true]],
                ['handle' => 'date_with_time_and_custom_format', 'field' => ['type' => 'date', 'time_enabled' => true, 'format' => 'U']],
                ['handle' => 'date_without_time', 'field' => ['type' => 'date']],
                ['handle' => 'date_range', 'field' => ['type' => 'date', 'mode' => 'range']],
                ['handle' => 'group_field', 'field' => ['type' => 'group', 'fields' => [
                    ['handle' => 'date_and_time', 'field' => ['type' => 'date', 'time_enabled' => true]],
                    ['handle' => 'date_range', 'field' => ['type' => 'date', 'mode' => 'range']],
                ]]],
                ['handle' => 'grid_field', 'field' => ['type' => 'grid', 'fields' => [
                    ['handle' => 'date_and_time', 'field' => ['type' => 'date', 'time_enabled' => true]],
                    ['handle' => 'date_range', 'field' => ['type' => 'date', 'mode' => 'range']],
                    ['handle' => 'second_grid_field', 'field' => ['type' => 'grid', 'fields' => [
                        ['handle' => 'date_and_time', 'field' => ['type' => 'date', 'time_enabled' => true]],
                        ['handle' => 'date_range', 'field' => ['type' => 'date', 'mode' => 'range']],
                    ]]],
                ]]],
                ['handle' => 'replicator_field', 'field' => ['type' => 'replicator', 'sets' => [
                    'set_group' => ['sets' => [
                        'set_name' => ['fields' => [
                            ['handle' => 'date_and_time', 'field' => ['type' => 'date', 'time_enabled' => true]],
                            ['handle' => 'date_range', 'field' => ['type' => 'date', 'mode' => 'range']],
                        ]],
                    ]],
                ]]],
                ['handle' => 'bard_field', 'field' => ['type' => 'bard', 'sets' => [
                    'set_group' => ['sets' => [
                        'set_name' => ['fields' => [
                            ['handle' => 'date_and_time', 'field' => ['type' => 'date', 'time_enabled' => true]],
                            ['handle' => 'date_range', 'field' => ['type' => 'date', 'mode' => 'range']],
                        ]],
                    ]],
                ]]],
            ],
        ])->save();

        $entry = Entry::make()
            ->collection('articles')
            ->date('2025-01-01-1200')
            ->data([
                'date_with_time' => '2025-01-01 12:00',
                'date_with_time_and_seconds' => '2025-01-01 12:00:15',
                'date_with_time_and_custom_format' => 1735689600,
                'date_without_time' => '2025-01-01',
                'date_range' => ['start' => '2025-01-01', 'end' => '2025-01-07'],
                'group_field' => ['date_and_time' => '2025-01-01 12:00', 'date_range' => ['start' => '2025-01-01', 'end' => '2025-01-07']],
                'grid_field' => [
                    ['date_and_time' => '2025-01-01 12:00', 'date_range' => ['start' => '2025-01-01', 'end' => '2025-01-07'], 'second_grid_field' => [
                        ['date_and_time' => '2025-01-01 12:00', 'date_range' => ['start' => '2025-01-01', 'end' => '2025-01-07']],
                    ]],
                ],
                'replicator_field' => [
                    ['type' => 'set_name', 'date_and_time' => '2025-01-01 12:00', 'date_range' => ['start' => '2025-01-01', 'end' => '2025-01-07']],
                ],
                'bard_field' => [
                    ['type' => 'set', 'attrs' => ['id' => 'abc', 'values' => [
                        'type' => 'set_name',
                        'date_and_time' => '2025-01-01 12:00',
                        'date_range' => ['start' => '2025-01-01', 'end' => '2025-01-07'],
                    ]]],
                ],
            ]);

        $entry->save();

        $this->runUpdateScript(ConvertDatesToUtc::class);

        $entry->fresh();

        $this->assertEquals('2025-01-01 17:00', $entry->date()->format('Y-m-d H:i'));
        $this->assertEquals('2025-01-01 17:00', $entry->get('date_with_time'));
        $this->assertEquals('2025-01-01 17:00:15', $entry->get('date_with_time_and_seconds'));
        $this->assertEquals(1735689600, $entry->get('date_with_time_and_custom_format'));
        $this->assertEquals('2025-01-01', $entry->get('date_without_time'));

        $this->assertEquals([
            'date_and_time' => '2025-01-01 17:00',
            'date_range' => ['start' => '2025-01-01', 'end' => '2025-01-07'],
        ], $entry->get('group_field'));

        $this->assertEquals([
            ['date_and_time' => '2025-01-01 17:00', 'date_range' => ['start' => '2025-01-01', 'end' => '2025-01-07'], 'second_grid_field' => [
                ['date_and_time' => '2025-01-01 17:00', 'date_range' => ['start' => '2025-01-01', 'end' => '2025-01-07']],
            ]],
        ], $entry->get('grid_field'));

        $this->assertEquals([
            ['type' => 'set_name', 'date_and_time' => '2025-01-01 17:00', 'date_range' => ['start' => '2025-01-01', 'end' => '2025-01-07']],
        ], $entry->get('replicator_field'));

        $this->assertEquals([
            ['type' => 'set', 'attrs' => ['id' => 'abc', 'values' => [
                'type' => 'set_name',
                'date_and_time' => '2025-01-01 17:00',
                'date_range' => ['start' => '2025-01-01', 'end' => '2025-01-07'],
            ]]],
        ], $entry->get('bard_field'));
    }

    // todo: ensure fields inside fieldsets are updated
    // TODO: Refactor test to use data provider
    // TODO: Add tests for other content types (terms, globals, users)
}
