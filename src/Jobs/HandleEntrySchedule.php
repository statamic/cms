<?php

namespace Statamic\Jobs;

use Carbon\CarbonInterface;
use Cron\CronExpression;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Statamic\Entries\ScheduledEntriesBetween;
use Statamic\Events\EntryScheduleReached;

class HandleEntrySchedule implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    const CACHE_KEY = 'statamic.scheduled-entries.checked-until';

    protected CarbonInterface $minute;

    public function __construct()
    {
        // We want to target up to the PREVIOUS minute because we can be sure that any entries that
        // were scheduled for then would now be considered published. If we were targeting
        // the current minute and the entry has defined a time with seconds later in the
        // same minute, it may still be considered scheduled when it gets dispatched.
        $this->minute = now()->subMinute();
    }

    public function handle()
    {
        $until = $this->minute->copy()->addMinute()->startOfMinute();
        $from = $this->from($until);

        if ($from->gte($until)) {
            return;
        }

        $this->entries($from, $until)->each(fn ($entry) => EntryScheduleReached::dispatch($entry));

        $this->markCheckedUntil($until);
    }

    private function entries(CarbonInterface $from, CarbonInterface $until): Collection
    {
        return (new ScheduledEntriesBetween($from, $until))();
    }

    private function from(CarbonInterface $until): CarbonInterface
    {
        $previousRun = $this->previousRunDate($until);

        // Without a record of the last check (first run, cache cleared), assume the
        // previous scheduled run happened. With the default frequency, that's the
        // previous minute.
        if (! $checkedUntil = Cache::get(self::CACHE_KEY)) {
            return $previousRun;
        }

        // Catch up on everything since the last check, but don't replay more than a
        // day (or one interval, if that's longer) when the job hasn't run for a while.
        return Carbon::createFromTimestamp($checkedUntil, $until->getTimezone())
            ->max($previousRun->min($until->copy()->subDay()));
    }

    private function previousRunDate(CarbonInterface $until): CarbonInterface
    {
        $expression = new CronExpression(config('statamic.system.scheduled_entries_frequency', '* * * * *'));
        $timezone = config('app.schedule_timezone') ?? $until->getTimezone()->getName();

        return Carbon::instance($expression->getPreviousRunDate($until, 0, false, $timezone));
    }

    private function markCheckedUntil(CarbonInterface $until): void
    {
        // Overlapping or out of order jobs should never move the marker backwards.
        if (Cache::get(self::CACHE_KEY, 0) >= $until->getTimestamp()) {
            return;
        }

        Cache::forever(self::CACHE_KEY, $until->getTimestamp());
    }
}
