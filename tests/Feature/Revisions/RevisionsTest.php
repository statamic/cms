<?php

namespace Tests\Feature\Revisions;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Carbon;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Statamic\Revisions\Revision;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class RevisionsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function a_revision_can_be_made_from_an_entry()
    {
        config(['statamic.revisions.path' => '/path/to']);

        Carbon::setTestNow($now = Carbon::parse('2019-03-25 13:15'));

        $revision = EntryFactory::id('123')
            ->collection(Collection::make('test'))
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
        $this->assertEquals(['id' => '123', 'published' => true, 'slug' => 'my-entry', 'data' => ['foo' => 'bar']], $revision->attributes());
        $this->assertEquals('123', $revision->attribute('id'));
        $this->assertEquals('/path/to/collections/test/en/123/'.$now->timestamp.'.yaml', $revision->path());
    }
}
