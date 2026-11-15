<?php

namespace Tests\Data\Entries;

use Carbon\Carbon;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\MinuteEntries;
use Statamic\Entries\ScheduledEntriesBetween;
use Statamic\Events\EntryScheduleReached;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Jobs\HandleEntrySchedule;
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

    #[Test]
    public function it_gets_entries_scheduled_between_two_times()
    {
        $this->createScheduledEntries([
            '01' => '2023-09-13-115959', // before window
            '02' => '2023-09-13-120000', // start of window (inclusive)
            '03' => '2023-09-13-123030', // within window
            '04' => '2023-09-13-125959', // end of window
            '05' => '2023-09-13-130000', // after window (exclusive)
        ]);

        $ids = (new ScheduledEntriesBetween(
            Carbon::parse('2023-09-13 12:00:00'),
            Carbon::parse('2023-09-13 13:00:00'),
        ))()->map->id->sort()->values()->all();

        $this->assertEquals(['02', '03', '04'], $ids);
    }

    #[Test]
    public function it_handles_entries_that_became_due_since_the_last_check()
    {
        $this->createScheduledEntries([
            '01' => '2023-09-13-115959', // already handled
            '02' => '2023-09-13-120000',
            '03' => '2023-09-13-120730',
            '04' => '2023-09-13-121459',
            '05' => '2023-09-13-121500', // current minute, next run
        ]);

        Cache::forever(HandleEntrySchedule::CACHE_KEY, Carbon::parse('2023-09-13 12:00:00')->getTimestamp());

        $this->runJobAt('2023-09-13 12:15:20');

        $this->assertEquals(['02', '03', '04'], $this->handledEntryIds());
        $this->assertEquals(Carbon::parse('2023-09-13 12:15:00')->getTimestamp(), Cache::get(HandleEntrySchedule::CACHE_KEY));
    }

    #[Test]
    public function it_handles_the_previous_minute_when_nothing_has_been_checked_yet()
    {
        $this->createScheduledEntries([
            '01' => '2023-09-13-121359',
            '02' => '2023-09-13-121400',
            '03' => '2023-09-13-121459',
            '04' => '2023-09-13-121500',
        ]);

        $this->runJobAt('2023-09-13 12:15:20');

        $this->assertEquals(['02', '03'], $this->handledEntryIds());
    }

    #[Test]
    public function it_handles_the_previous_interval_when_nothing_has_been_checked_yet()
    {
        config(['statamic.system.scheduled_entries_frequency' => '0 * * * *']);

        $this->createScheduledEntries([
            '01' => '2023-09-13-115959',
            '02' => '2023-09-13-120000',
            '03' => '2023-09-13-125959',
            '04' => '2023-09-13-130000',
        ]);

        $this->runJobAt('2023-09-13 13:00:10');

        $this->assertEquals(['02', '03'], $this->handledEntryIds());
    }

    #[Test]
    public function it_doesnt_look_back_further_than_a_day()
    {
        $this->createScheduledEntries([
            '01' => '2023-09-12-121459', // more than a day ago
            '02' => '2023-09-12-121500',
            '03' => '2023-09-13-111400',
        ]);

        Cache::forever(HandleEntrySchedule::CACHE_KEY, Carbon::parse('2023-09-10 12:00:00')->getTimestamp());

        $this->runJobAt('2023-09-13 12:15:20');

        $this->assertEquals(['02', '03'], $this->handledEntryIds());
    }

    #[Test]
    public function it_looks_back_a_full_interval_when_the_frequency_is_longer_than_a_day()
    {
        config(['statamic.system.scheduled_entries_frequency' => '0 0 * * 0']);

        $this->createScheduledEntries([
            '01' => '2023-09-09-235959', // before the previous run
            '02' => '2023-09-10-000000',
            '03' => '2023-09-14-120000',
            '04' => '2023-09-16-235959',
        ]);

        Cache::forever(HandleEntrySchedule::CACHE_KEY, Carbon::parse('2023-09-03 00:00:00')->getTimestamp());

        $this->runJobAt('2023-09-17 00:00:10');

        $this->assertEquals(['02', '03', '04'], $this->handledEntryIds());
    }

    #[Test]
    public function it_doesnt_move_the_checked_marker_backwards()
    {
        $this->createScheduledEntries(['01' => '2023-09-13-121400']);

        $marker = Carbon::parse('2023-09-13 12:30:00')->getTimestamp();
        Cache::forever(HandleEntrySchedule::CACHE_KEY, $marker);

        $this->runJobAt('2023-09-13 12:15:20');

        $this->assertEquals([], $this->handledEntryIds());
        $this->assertEquals($marker, Cache::get(HandleEntrySchedule::CACHE_KEY));
    }

    private function createScheduledEntries(array $entries)
    {
        $this->makeCollectionsWithBlueprints([
            'scheduled' => [
                'date' => ['type' => 'date', 'time_enabled' => true, 'time_seconds_enabled' => true],
            ],
            'undated' => [],
        ]);

        Collection::find('undated')->dated(false)->save();
        EntryFactory::id('undated')->collection('undated')->create();

        collect($entries)->each(fn ($date, $id) => EntryFactory::id($id)->collection('scheduled')->date($date)->create());
    }

    private function runJobAt($time)
    {
        $this->travelTo(Carbon::parse($time));

        Event::fake([EntryScheduleReached::class]);

        (new HandleEntrySchedule)->handle();
    }

    private function handledEntryIds()
    {
        return Event::dispatched(EntryScheduleReached::class)
            ->map(fn ($args) => $args[0]->entry->id())
            ->sort()->values()->all();
    }

    #[Test]
    public function it_schedules_the_job_every_minute()
    {
        $events = $this->scheduledJobEvents();

        $this->assertCount(1, $events);
        $this->assertEquals('* * * * *', $events->first()->expression);
    }

    #[Test]
    #[DefineEnvironment('disableHandlingOfScheduledEntries')]
    public function it_doesnt_schedule_the_job_when_handling_scheduled_entries_is_disabled()
    {
        $this->assertCount(0, $this->scheduledJobEvents());
    }

    public function disableHandlingOfScheduledEntries($app)
    {
        $app['config']->set('statamic.system.handle_scheduled_entries', false);
    }

    #[Test]
    #[DefineEnvironment('scheduleEntriesEveryFifteenMinutes')]
    public function it_schedules_the_job_at_the_configured_frequency()
    {
        $events = $this->scheduledJobEvents();

        $this->assertCount(1, $events);
        $this->assertEquals('*/15 * * * *', $events->first()->expression);
    }

    public function scheduleEntriesEveryFifteenMinutes($app)
    {
        $app['config']->set('statamic.system.scheduled_entries_frequency', '*/15 * * * *');
    }

    private function scheduledJobEvents()
    {
        return collect($this->app->make(Schedule::class)->events())
            ->filter(fn ($event) => $event->description === HandleEntrySchedule::class);
    }
}
