<?php

namespace Tests\Jobs;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\EntryScheduleReached;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Statamic\Jobs\HandleRevisionSchedule;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class HandleRevisionScheduleTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        config(['statamic.revisions.enabled' => true]);
        Collection::make('blog')->revisionsEnabled(true)->save();
        Carbon::setTestNow(Carbon::parse('2026-09-02 10:00'));
    }

    #[Test]
    public function it_publishes_due_revisions()
    {
        Event::fake([EntryScheduleReached::class]);
        $user = User::make()->id('user-1')->save();

        $this->scheduleRevision('1', 'Scheduled', '2026-09-01 08:00', ['user' => $user, 'message' => 'Go live']);
        $this->scheduleRevision('2', 'Scheduled', '2026-09-02 12:01');
        $unscheduled = EntryFactory::id('3')->collection('blog')->published(false)->data(['title' => 'Original'])->create();
        $unscheduled->createRevision();

        $this->runJobAt('2026-09-02 12:00');

        $entry = Entry::find('1');
        $this->assertTrue($entry->published());
        $this->assertEquals('Scheduled', $entry->get('title'));
        $this->assertEquals('user-1', $entry->lastModifiedBy()->id());
        $this->assertFalse($entry->hasWorkingCopy());
        $this->assertCount(2, $entry->revisions());
        $this->assertNull($entry->revisions()->first()->publishAt());
        $published = $entry->latestRevision();
        $this->assertEquals('publish', $published->action());
        $this->assertEquals('Go live', $published->message());
        $this->assertEquals('user-1', $published->user()->id());
        $this->assertEquals(['title' => 'Scheduled'], $published->attributes()['data']);
        Event::assertDispatched(EntryScheduleReached::class, fn ($event) => $event->entry->id() === '1');

        $entry = Entry::find('2');
        $this->assertFalse($entry->published());
        $this->assertEquals('Original', $entry->get('title'));
        $this->assertTrue($entry->hasWorkingCopy());
        $this->assertCount(1, $entry->revisions());
        $this->assertEquals(Carbon::parse('2026-09-02 12:01'), $entry->latestRevision()->publishAt());
        Event::assertNotDispatched(EntryScheduleReached::class, fn ($event) => $event->entry->id() === '2');

        $entry = Entry::find('3');
        $this->assertFalse($entry->published());
        $this->assertCount(1, $entry->revisions());
        Event::assertNotDispatched(EntryScheduleReached::class, fn ($event) => $event->entry->id() === '3');
    }

    #[Test]
    public function it_does_not_republish_an_already_applied_revision()
    {
        Event::fake([EntryScheduleReached::class]);
        $this->scheduleRevision('1', 'Scheduled', '2026-09-02 11:00');

        $this->runJobAt('2026-09-02 12:00');
        $this->runJobAt('2026-09-02 12:01');

        $this->assertCount(2, Entry::find('1')->revisions());
        Event::assertDispatchedTimes(EntryScheduleReached::class, 1);
    }

    #[Test]
    public function it_applies_multiple_due_revisions_in_order()
    {
        $this->scheduleRevision('1', 'Second', '2026-09-02 11:30');
        Carbon::setTestNow(Carbon::parse('2026-09-02 10:01'));
        $this->scheduleRevision('1', 'First', '2026-09-02 11:00');

        $this->runJobAt('2026-09-02 12:00');

        $entry = Entry::find('1');
        $this->assertTrue($entry->published());
        $this->assertEquals('Second', $entry->get('title'));
        $this->assertEquals(
            [null, null],
            $entry->revisions()->filter(fn ($revision) => $revision->action() === 'revision')->map->publishAt()->values()->all()
        );
    }

    #[Test]
    public function it_keeps_a_working_copy_that_changed_after_scheduling()
    {
        $this->scheduleRevision('1', 'Scheduled', '2026-09-02 11:00');
        $entry = Entry::find('1');
        tap($entry->workingCopy(), function ($copy) {
            $attrs = $copy->attributes();
            $attrs['data']['title'] = 'Newer edits';
            $copy->attributes($attrs);
        })->save();

        $this->runJobAt('2026-09-02 12:00');

        $entry = Entry::find('1');
        $this->assertTrue($entry->published());
        $this->assertEquals('Scheduled', $entry->get('title'));
        $this->assertTrue($entry->hasWorkingCopy());
        $this->assertEquals('Newer edits', $entry->workingCopy()->attributes()['data']['title']);
    }

    #[Test]
    public function it_skips_revisions_whose_entry_no_longer_exists()
    {
        Event::fake([EntryScheduleReached::class]);
        $this->scheduleRevision('1', 'Scheduled', '2026-09-02 11:00');
        Entry::find('1')->delete();

        $this->runJobAt('2026-09-02 12:00');

        $this->assertNull(Entry::find('1'));
        Event::assertNotDispatched(EntryScheduleReached::class);
    }

    private function runJobAt($time)
    {
        Carbon::setTestNow(Carbon::parse($time));

        (new HandleRevisionSchedule)->handle();
    }

    private function scheduleRevision($id, $title, $publishAt, $options = [])
    {
        $entry = Entry::find($id) ?? EntryFactory::id($id)->collection('blog')->published(false)->data(['title' => 'Original'])->create();

        if (! $entry->hasWorkingCopy()) {
            $entry->makeWorkingCopy()->save();
        }

        tap($entry->workingCopy(), function ($copy) use ($title) {
            $attrs = $copy->attributes();
            $attrs['data']['title'] = $title;
            $copy->attributes($attrs);
        })->save();

        $entry->createRevision($options + ['publish_at' => Carbon::parse($publishAt)]);
    }
}
