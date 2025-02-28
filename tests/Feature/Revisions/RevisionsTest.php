<?php

namespace Tests\Feature\Revisions;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Statamic\Revisions\Revision;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class RevisionsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function a_revision_can_be_made_from_an_entry()
    {
        config(['statamic.revisions.path' => '/path/to']);

        Carbon::setTestNow($now = Carbon::parse('2019-03-25 13:15'));

        $revision = EntryFactory::id('123')
            ->collection(tap(Collection::make('test'))->save())
            ->slug('my-entry')
            ->data(['foo' => 'bar'])
            ->make()
            ->makeRevision();

        $revision
            ->user($user = User::make())
            ->message('test');

        $this->assertInstanceOf(Revision::class, $revision);
        $this->assertEquals('test', $revision->message());
        $this->assertEquals($user, $revision->user());
        $this->assertEquals($now, $revision->date());
        $this->assertEquals('collections/test/en/123', $revision->key());
        $this->assertEquals(['id' => '123', 'published' => true, 'slug' => 'my-entry', 'data' => ['foo' => 'bar'], 'date' => null], $revision->attributes());
        $this->assertEquals('123', $revision->attribute('id'));
        $this->assertEquals('/path/to/collections/test/en/123/'.$now->timestamp.'.yaml', $revision->path());
    }

    #[Test]
    public function a_revision_can_be_made_from_a_dated_entry()
    {
        config(['statamic.revisions.path' => '/path/to']);

        Carbon::setTestNow($now = Carbon::parse('2019-03-25 13:15'));

        $revision = EntryFactory::id('123')
            ->collection(tap(Collection::make('test')->dated(true))->save())
            ->slug('my-entry')
            ->data(['foo' => 'bar'])
            ->date('2016-12-25')
            ->make()
            ->makeRevision();

        $this->assertEquals(['id' => '123', 'published' => true, 'slug' => 'my-entry', 'data' => ['foo' => 'bar'], 'date' => '1482624000'], $revision->attributes());
    }

    #[Test]
    public function can_get_its_entry()
    {
        config(['statamic.revisions.path' => '/path/to']);

        Carbon::setTestNow($now = Carbon::parse('2019-03-25 13:15'));

        $entry = EntryFactory::id('123')
            ->collection(tap(Collection::make('test')->dated(true))->save())
            ->slug('my-entry')
            ->data(['foo' => 'bar'])
            ->date('2016-12-25')
            ->make();

        $entry->save();
        $revision = $entry->makeRevision();

        $this->assertEquals($entry->id(), $revision->currentContent()->id());
    }

    #[Test]
    public function converts_publish_at_to_timestamp_when_saving()
    {
        Carbon::setTestNow(now());

        $revision = (new Revision)
            ->date(now())
            ->publishAt(now());

        $this->assertEquals(now()->timestamp, $revision->fileData()['publish_at']);
    }

    #[Test]
    public function converts_publish_at_to_null_when_saving()
    {
        $revision = (new Revision)->date(now());

        $this->assertNull($revision->fileData()['publish_at']);
    }

    #[Test]
    public function outputs_publish_at_when_to_array()
    {
        Carbon::setTestNow(now());

        $revision = (new Revision)
            ->date(now())
            ->publishAt(now());

        $this->assertEquals(now(), $revision->toArray()['publish_at']);
    }
}
