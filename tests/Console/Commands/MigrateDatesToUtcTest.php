<?php

namespace Tests\Console\Commands;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Fieldset;
use Statamic\Facades\File;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class MigrateDatesToUtcTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.timezone', 'America/New_York'); // -05:00
        date_default_timezone_set('America/New_York');

        Fieldset::make('date_fieldset')->setContents(['fields' => [
            ['handle' => 'fieldset_date', 'field' => ['type' => 'date', 'time_enabled' => true]],
        ]])->save();
    }

    public function tearDown(): void
    {
        File::delete(base_path('resources/blueprints'));
        File::delete(base_path('resources/fieldsets'));

        parent::tearDown();
    }

    #[Test]
    #[DataProvider('dateFieldsProvider')]
    public function it_converts_date_fields_in_entries(string $fieldHandle, array $field, $original, $expected)
    {
        $collection = tap(Collection::make('articles'))->save();
        $collection->entryBlueprint()->setContents(['fields' => [$field]])->save();

        $entry = Entry::make()->collection('articles')->data([$fieldHandle => $original]);
        $entry->save();

        $this->migrateDatesToUtc();

        $this->assertEquals($expected, $entry->fresh()->get($fieldHandle));
    }

    #[Test]
    public function it_converts_entry_date_field_in_entries()
    {
        $collection = tap(Collection::make('articles')->dated(true))->save();

        $collection->entryBlueprint()->setContents([
            'fields' => [
                ['handle' => 'date', 'field' => ['type' => 'date', 'time_enabled' => true]],
            ],
        ])->save();

        $entry = Entry::make()->id('foo')->collection('articles')->date('2025-01-01-1200');
        $entry->save();

        $this->migrateDatesToUtc();

        $entry = Entry::find($entry->id());

        $this->assertEquals('2025-01-01 17:00', $entry->date()->format('Y-m-d H:i'));
        $this->assertStringContainsString('2025-01-01-1700.foo.md', $entry->buildPath());
    }

    #[Test]
    public function it_converts_entry_date_field_in_entries_when_app_timezone_is_utc()
    {
        config()->set('app.timezone', 'UTC');
        date_default_timezone_set('UTC');

        $this->it_converts_entry_date_field_in_entries();
    }

    #[Test]
    #[DataProvider('dateFieldsProvider')]
    public function it_converts_date_fields_in_terms(string $fieldHandle, array $field, $original, $expected)
    {
        $taxonomy = tap(Taxonomy::make('tags'))->save();
        $taxonomy->termBlueprint()->setContents(['fields' => [$field]])->save();

        $term = Term::make()->taxonomy('tags')->data([$fieldHandle => $original]);
        $term->save();

        $this->migrateDatesToUtc();

        $this->assertEquals($expected, $term->fresh()->get($fieldHandle));
    }

    #[Test]
    #[DataProvider('dateFieldsProvider')]
    public function it_converts_date_fields_in_globals(string $fieldHandle, array $field, $original, $expected)
    {
        $globalSet = tap(GlobalSet::make('settings'))->save();
        $globalSet->addLocalization(
            $globalSet->makeLocalization('en')->data([$fieldHandle => $original])
        );
        $globalSet->save();

        Blueprint::make('settings')->setNamespace('globals')->setContents(['fields' => [$field]])->save();

        $this->migrateDatesToUtc();

        $globalSet = GlobalSet::find('settings');

        $this->assertEquals($expected, $globalSet->inDefaultSite()->get($fieldHandle));
    }

    #[Test]
    #[DataProvider('dateFieldsProvider')]
    public function it_converts_date_fields_in_users(string $fieldHandle, array $field, $original, $expected)
    {
        User::blueprint()->setContents(['fields' => [$field]])->save();

        $user = User::make()->data([$fieldHandle => $original]);
        $user->save();

        $this->migrateDatesToUtc();

        $this->assertEquals($expected, $user->fresh()->get($fieldHandle));
    }

    public static function dateFieldsProvider(): array
    {
        return [
            'Date field' => [
                'date_field',
                ['handle' => 'date_field', 'field' => ['type' => 'date']],
                '2025-01-01',
                '2025-01-01 05:00',
            ],
            'Date field with time enabled' => [
                'date_field',
                ['handle' => 'date_field', 'field' => ['type' => 'date', 'time_enabled' => true]],
                '2025-01-01 12:00',
                '2025-01-01 17:00',
            ],
            'Date field with time and seconds enabled' => [
                'date_field',
                ['handle' => 'date_field', 'field' => ['type' => 'date', 'time_enabled' => true, 'time_seconds_enabled' => true]],
                '2025-01-01 12:00:15',
                '2025-01-01 17:00:15',
            ],
            'Date field with time enabled, and a custom format' => [
                'date_field',
                ['handle' => 'date_field', 'field' => ['type' => 'date', 'time_enabled' => true, 'format' => 'U']],
                1735689600,
                1735689600,
            ],
            'Date range' => [
                'date_field',
                ['handle' => 'date_field', 'field' => ['type' => 'date', 'mode' => 'range']],
                ['start' => '2025-01-01', 'end' => '2025-01-07'],
                ['start' => '2025-01-01 05:00', 'end' => '2025-01-07 05:00'],
            ],
            'Date range, but stored as a single date' => [
                'date_field',
                ['handle' => 'date_field', 'field' => ['type' => 'date', 'mode' => 'range']],
                '2025-01-01',
                ['start' => '2025-01-01 05:00', 'end' => '2025-01-01 05:00'],
            ],
            'Imported date field' => [
                'fieldset_date',
                ['import' => 'date_fieldset'],
                '2025-01-01 12:00',
                '2025-01-01 17:00',
            ],
            'Group field with nested date fields' => [
                'group_field',
                ['handle' => 'group_field', 'field' => ['type' => 'group', 'fields' => [
                    ['handle' => 'date_and_time', 'field' => ['type' => 'date', 'time_enabled' => true]],
                    ['handle' => 'date_range', 'field' => ['type' => 'date', 'mode' => 'range']],
                ]]],
                [
                    'date_and_time' => '2025-01-01 12:00',
                    'date_range' => ['start' => '2025-01-01', 'end' => '2025-01-07'],
                ],
                [
                    'date_and_time' => '2025-01-01 17:00',
                    'date_range' => ['start' => '2025-01-01 05:00', 'end' => '2025-01-07 05:00'],
                ],
            ],
            'Grid field with nested date fields' => [
                'grid_field',
                ['handle' => 'grid_field', 'field' => ['type' => 'grid', 'fields' => [
                    ['handle' => 'date_and_time', 'field' => ['type' => 'date', 'time_enabled' => true]],
                    ['handle' => 'date_range', 'field' => ['type' => 'date', 'mode' => 'range']],
                ]]],
                [['date_and_time' => '2025-01-01 12:00', 'date_range' => ['start' => '2025-01-01', 'end' => '2025-01-07']]],
                [['date_and_time' => '2025-01-01 17:00', 'date_range' => ['start' => '2025-01-01 05:00', 'end' => '2025-01-07 05:00']]],
            ],
            'Replicator field with nested date fields' => [
                'replicator_field',
                ['handle' => 'replicator_field', 'field' => ['type' => 'replicator', 'sets' => [
                    'set_group' => ['sets' => [
                        'set_name' => ['fields' => [
                            ['handle' => 'date_and_time', 'field' => ['type' => 'date', 'time_enabled' => true]],
                            ['handle' => 'date_range', 'field' => ['type' => 'date', 'mode' => 'range']],
                        ]],
                    ]],
                ]]],
                [['type' => 'set_name', 'date_and_time' => '2025-01-01 12:00', 'date_range' => ['start' => '2025-01-01', 'end' => '2025-01-07']]],
                [['type' => 'set_name', 'date_and_time' => '2025-01-01 17:00', 'date_range' => ['start' => '2025-01-01 05:00', 'end' => '2025-01-07 05:00']]],
            ],
            'Bard field with nested date fields' => [
                'bard_field',
                ['handle' => 'bard_field', 'field' => ['type' => 'bard', 'sets' => [
                    'set_group' => ['sets' => [
                        'set_name' => ['fields' => [
                            ['handle' => 'date_and_time', 'field' => ['type' => 'date', 'time_enabled' => true]],
                            ['handle' => 'date_range', 'field' => ['type' => 'date', 'mode' => 'range']],
                        ]],
                    ]],
                ]]],
                [['type' => 'set', 'attrs' => ['id' => 'abc', 'values' => [
                    'type' => 'set_name',
                    'date_and_time' => '2025-01-01 12:00',
                    'date_range' => ['start' => '2025-01-01', 'end' => '2025-01-07'],
                ]]]],
                [['type' => 'set', 'attrs' => ['id' => 'abc', 'values' => [
                    'type' => 'set_name',
                    'date_and_time' => '2025-01-01 17:00',
                    'date_range' => ['start' => '2025-01-01 05:00', 'end' => '2025-01-07 05:00'],
                ]]]],
            ],
            'Deeply nested date fields' => [
                'deeply_nested_date_fields',
                ['handle' => 'deeply_nested_date_fields', 'field' => ['type' => 'grid', 'fields' => [
                    ['handle' => 'nested_group', 'field' => ['type' => 'group', 'fields' => [
                        ['handle' => 'date_and_time', 'field' => ['type' => 'date', 'time_enabled' => true]],
                        ['handle' => 'date_range', 'field' => ['type' => 'date', 'mode' => 'range']],
                    ]]],
                ]]],
                [['nested_group' => [
                    'date_and_time' => '2025-01-01 12:00', 'date_range' => ['start' => '2025-01-01', 'end' => '2025-01-07'],
                ]]],
                [['nested_group' => [
                    'date_and_time' => '2025-01-01 17:00', 'date_range' => ['start' => '2025-01-01 05:00', 'end' => '2025-01-07 05:00'],
                ]]],
            ],
        ];
    }

    private function migrateDatesToUtc(): void
    {
        $this
            ->artisan('statamic:migrate-dates-to-utc', [
                'timezone' => 'America/New_York',
            ])
            ->expectsQuestion('Do you want to continue', true);
    }
}
