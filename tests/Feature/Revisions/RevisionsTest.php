<?php

namespace Tests\Feature\Revisions;

use Tests\TestCase;
use Statamic\API\User;
use Statamic\API\Entry;
use Statamic\API\Collection;
use Illuminate\Support\Carbon;
use Statamic\Revisions\Revision;
use Tests\PreventSavingStacheItemsToDisk;

class RevisionsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    function a_revision_can_be_made_from_an_entry()
    {
        config(['statamic.revisions.path' => '/path/to']);

        Carbon::setTestNow($now = Carbon::parse('2019-03-25 13:15'));

        $revision = Entry::make()
            ->id('123')
            ->collection(Collection::make('test'))
            ->in('default', function ($loc) {
                $loc->slug('my-entry')->data(['foo' => 'bar']);
            })->makeRevision();

        $revision
            ->user($user = User::make())
            ->message('test');

        $this->assertInstanceOf(Revision::class, $revision);
        $this->assertEquals('test', $revision->message());
        $this->assertEquals($user, $revision->user());
        $this->assertEquals($now, $revision->date());
        $this->assertEquals('collections/test/default/123', $revision->key());
        $this->assertEquals(['slug' => 'my-entry', 'data' => ['foo' => 'bar']], $revision->attributes());
        $this->assertEquals('/path/to/collections/test/default/123/'.$now->timestamp.'.yaml', $revision->path());
    }
}
