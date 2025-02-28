<?php

namespace Tests\Data\Entries;

use Carbon\Carbon;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\MinuteRevisions;
use Statamic\Facades\Collection;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ScheduledRevisionsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();
        $this->dir = __DIR__.'/tmp';
        config(['statamic.revisions.enabled' => true]);
        config(['statamic.revisions.path' => $this->dir]);
    }

    public function tearDown(): void
    {
        File::deleteDirectory($this->dir);
        parent::tearDown();
    }

    #[Test]
    public function it_gets_entries_scheduled_for_given_minute()
    {
        Carbon::setTestNow($now = now()->setSeconds(2)->toImmutable());

        Collection::make('revisable')->revisionsEnabled(true)->save();

        EntryFactory::id(1)
            ->collection('revisable')
            ->create()
            ->makeRevision()
            ->publishAt($now->addSeconds(5))
            ->save();

        EntryFactory::id(2)
            ->collection('revisable')
            ->create()
            ->makeRevision()
            ->save();

        EntryFactory::id(3)
            ->collection('revisable')
            ->create()
            ->makeRevision()
            ->publishAt($now->addSeconds(65))
            ->save();

        $this->assertEquals([1], $this->getRevisionsForMinute($now));
        $this->assertEmpty($this->getRevisionsForMinute($now->addMinutes(2)));
    }

    private function getRevisionsForMinute($minute)
    {
        return (new MinuteRevisions($minute))()->map->id()->all();
    }
}
