<?php

namespace Tests\Data\Entries;

use Carbon\Carbon;
use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\MinuteEntries;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ScheduledEntriesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function makeCollectionsWithBlueprints($collections)
    {
        $bps = [];

        foreach ($collections as $collection => $fields) {
            $bps[$collection] = Blueprint::makeFromFields($fields)->setHandle($collection);
        }

        foreach ($collections as $collection => $fields) {
            Collection::make($collection)->dated(true)->save();
            Blueprint::shouldReceive('in')->with('collections/'.$collection)->andReturn(collect(['test' => $bps[$collection]]));
        }
    }

    private function getEntryIdsForMinute($minute)
    {
        $minute = Carbon::parse($minute);

        return (new MinuteEntries($minute))()->map->id->all();
    }

    #[Test]
    public function it_gets_entries_scheduled_for_given_minute()
    {
        $this->makeCollectionsWithBlueprints([
            'time_with_seconds' => [
                'date' => ['type' => 'date', 'time_enabled' => true, 'time_seconds_enabled' => true],
            ],
            'time_without_seconds' => [
                'date' => ['type' => 'date', 'time_enabled' => true, 'time_seconds_enabled' => false],
            ],
            'dated' => [
                'date' => ['type' => 'date', 'time_enabled' => false],
            ],
            'undated' => [],
        ]);

        collect([
            '01' => '2023-09-12-121400', // day before
            '02' => '2023-09-12-121420', // day before
            '03' => '2023-09-12-121421', // day before
            '04' => '2023-09-13-121300', // minute before
            '05' => '2023-09-13-121320', // minute before
            '06' => '2023-09-13-121321', // minute before
            '07' => '2023-09-13-121400', // target minute
            '08' => '2023-09-13-121420', // target second
            '09' => '2023-09-13-121421', // target minute
            '10' => '2023-09-13-121500', // minute after
            '11' => '2023-09-13-121520', // minute after
            '12' => '2023-09-13-121521', // minute after
            '13' => '2023-09-14-121400', // day after
            '14' => '2023-09-14-121420', // day after
            '15' => '2023-09-14-121421', // day after
        ])->each(fn ($date, $id) => EntryFactory::id($id)->collection('time_with_seconds')->date($date)->create());

        collect([
            '16' => '2023-09-12-1214', // day before
            '17' => '2023-09-13-1213', // minute before
            '18' => '2023-09-13-1214', // target minute
            '19' => '2023-09-13-1215', // minute after
            '20' => '2023-09-14-1214', // day after
        ])->each(fn ($date, $id) => EntryFactory::id($id)->collection('time_without_seconds')->date($date)->create());

        collect([
            '21' => '2023-09-12', // day before
            '22' => '2023-09-13', // target minute's day
            '23' => '2023-09-14', // day after
        ])->each(fn ($date, $id) => EntryFactory::id($id)->collection('dated')->date($date)->create());

        EntryFactory::id('24')->collection('undated')->create();

        $this->assertEquals(['07', '08', '09', '18'], $this->getEntryIdsForMinute('2023-09-13 12:14:20'));
        $this->assertEquals(['07', '08', '09', '18'], $this->getEntryIdsForMinute('2023-09-13 12:14:00'));
        $this->assertEquals(['07', '08', '09', '18'], $this->getEntryIdsForMinute('2023-09-13 12:14:25'));
        $this->assertEquals(['22'], $this->getEntryIdsForMinute('2023-09-13 00:00:00'));
    }
}
