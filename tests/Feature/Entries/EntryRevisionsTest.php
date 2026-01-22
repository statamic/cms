<?php

namespace Tests\Feature\Entries;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Folder;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;
use Statamic\Revisions\Revision;
use Statamic\Revisions\WorkingCopy;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EntryRevisionsTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private $dir;
    private $collection;

    public function setUp(): void
    {
        parent::setUp();
        $this->dir = __DIR__.'/tmp';
        config(['statamic.revisions.enabled' => true]);
        config(['statamic.revisions.path' => $this->dir]);
        $this->collection = tap(Collection::make('blog')->revisionsEnabled(true)->dated(true))->save();
    }

    public function tearDown(): void
    {
        Folder::delete($this->dir);
        parent::tearDown();
    }

    #[Test]
    public function it_gets_revisions()
    {
        $now = Carbon::parse('2017-02-03');
        Carbon::setTestNow($now);
        $this->setTestBlueprint('test', ['foo' => ['type' => 'text']]);
        $this->setTestRoles(['test' => ['access cp', 'publish blog entries']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $entry = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->published(true)
            ->date('2010-12-25')
            ->data([
                'blueprint' => 'test',
                'title' => 'Original title',
                'foo' => 'bar',
            ])->create();

        tap($entry->makeRevision(), function ($copy) {
            $copy->message('Revision one');
            $copy->date(Carbon::parse('2017-02-01'));
        })->save();

        tap($entry->makeRevision(), function ($copy) {
            $copy->message('Revision two');
            $copy->date(Carbon::parse('2017-02-03'));
        })->save();

        tap($entry->makeWorkingCopy(), function ($copy) {
            $attrs = $copy->attributes();
            $attrs['data']['title'] = 'Title modified in working copy';
            $attrs['data']['foo'] = 'baz';
            $copy->attributes($attrs);
        })->save();

        $this
            ->actingAs($user)
            ->get($entry->revisionsUrl())
            ->assertOk()
            ->assertJsonPath('0.revisions.0.action', 'revision')
            ->assertJsonPath('0.revisions.0.message', 'Revision one')
            ->assertJsonPath('0.revisions.0.attributes.data.title', 'Original title')
            ->assertJsonPath('0.revisions.0.attributes.item_url', 'http://localhost/cp/collections/blog/entries/1/revisions/'.Carbon::parse('2017-02-01')->timestamp)

            ->assertJsonPath('1.revisions.0.action', 'revision')
            ->assertJsonPath('1.revisions.0.message', false)
            ->assertJsonPath('1.revisions.0.attributes.data.title', 'Title modified in working copy')
            ->assertJsonPath('1.revisions.0.attributes.item_url', null)

            ->assertJsonPath('1.revisions.1.action', 'revision')
            ->assertJsonPath('1.revisions.1.message', 'Revision two')
            ->assertJsonPath('1.revisions.1.attributes.data.title', 'Original title')
            ->assertJsonPath('1.revisions.1.attributes.item_url', 'http://localhost/cp/collections/blog/entries/1/revisions/'.Carbon::parse('2017-02-03')->timestamp);
    }

    #[Test]
    public function it_publishes_an_entry()
    {
        $now = Carbon::parse('2017-02-03');
        Carbon::setTestNow($now);
        $this->setTestBlueprint('test', ['foo' => ['type' => 'text']]);
        $this->setTestRoles(['test' => ['access cp', 'publish blog entries']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $entry = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->published(false)
            ->date('2010-12-25')
            ->data([
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'bar',
            ])->create();

        tap($entry->makeWorkingCopy(), function ($copy) {
            $attrs = $copy->attributes();
            $attrs['data']['foo'] = 'foo modified in working copy';
            $attrs['date'] = 1482624000; // 2016-12-25
            $copy->attributes($attrs);
        })->save();

        $this->assertFalse($entry->published());
        $this->assertCount(0, $entry->revisions());

        $this
            ->actingAs($user)
            ->publish($entry, ['message' => 'Test!'])
            ->assertOk();

        $entry = Entry::find($entry->id());
        $this->assertEquals([
            'blueprint' => 'test',
            'title' => 'Title',
            'foo' => 'foo modified in working copy',
            'updated_at' => $now->timestamp,
            'updated_by' => $user->id(),
        ], $entry->data()->all());
        $this->assertTrue($entry->published());
        $this->assertCount(1, $entry->revisions());
        $this->assertEquals('2016-12-25', $entry->date()->format('Y-m-d'));
        $revision = $entry->latestRevision();
        $this->assertEquals([
            'published' => true,
            'slug' => 'test',
            'id' => '1',
            'date' => 1482624000,
            'data' => [
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'foo modified in working copy',
            ],
        ], $revision->attributes());
        $this->assertEquals('user-1', $revision->user()->id());
        $this->assertEquals('Test!', $revision->message());
        $this->assertEquals('publish', $revision->action());
        $this->assertFalse($entry->hasWorkingCopy());
    }

    #[Test]
    public function it_unpublishes_an_entry()
    {
        $now = Carbon::parse('2017-02-03');
        Carbon::setTestNow($now);
        $this->setTestBlueprint('test', ['foo' => ['type' => 'text']]);
        $this->setTestRoles(['test' => ['access cp', 'publish blog entries']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $entry = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->published(true)
            ->date('2010-12-25')
            ->data([
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'bar',
            ])->create();

        $this->assertTrue($entry->published());
        $this->assertCount(0, $entry->revisions());

        $this
            ->actingAs($user)
            ->unpublish($entry, ['message' => 'Test!'])
            ->assertOk();

        $entry = Entry::find($entry->id());
        $this->assertEquals([
            'blueprint' => 'test',
            'title' => 'Title',
            'foo' => 'bar',
            'updated_at' => $now->timestamp,
            'updated_by' => $user->id(),
        ], $entry->data()->all());
        $this->assertFalse($entry->published());
        $this->assertCount(1, $entry->revisions());
        $revision = $entry->latestRevision();
        $this->assertEquals([
            'published' => false,
            'slug' => 'test',
            'id' => '1',
            'date' => 1293235200, // 2010-12-25
            'data' => [
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'bar',
            ],
        ], $revision->attributes());
        $this->assertEquals('user-1', $revision->user()->id());
        $this->assertEquals('Test!', $revision->message());
        $this->assertEquals('unpublish', $revision->action());
    }

    #[Test]
    public function it_creates_a_revision()
    {
        $this->setTestBlueprint('test', ['foo' => ['type' => 'text']]);
        $this->setTestRoles(['test' => ['access cp', 'edit blog entries']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $entry = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->published(false)
            ->date('2010-12-25')
            ->data([
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'bar',
            ])->create();

        tap($entry->makeWorkingCopy(), function ($copy) {
            $attrs = $copy->attributes();
            $attrs['data']['foo'] = 'foo modified in working copy';
            $copy->attributes($attrs);
        })->save();

        $this->assertFalse($entry->published());
        $this->assertCount(0, $entry->revisions());

        $this
            ->actingAs($user)
            ->post($entry->createRevisionUrl(), ['message' => 'Test!', 'publish_at' => ['date' => '2010-12-29', 'time' => '11:00am']])
            ->assertOk();

        $entry = Entry::find($entry->id());
        $this->assertEquals([
            'blueprint' => 'test',
            'title' => 'Title',
            'foo' => 'bar',
        ], $entry->data()->all());
        $this->assertFalse($entry->published());
        $this->assertCount(1, $entry->revisions());
        $revision = $entry->latestRevision();
        $this->assertEquals([
            'published' => false,
            'slug' => 'test',
            'id' => '1',
            'date' => 1293235200, // 2010-12-25
            'data' => [
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'foo modified in working copy',
            ],
        ], $revision->attributes());
        $this->assertEquals('user-1', $revision->user()->id());
        $this->assertEquals('Test!', $revision->message());
        $this->assertEquals(1293620400, $revision->publishAt()->timestamp);
        $this->assertEquals('revision', $revision->action());
        $this->assertTrue($entry->hasWorkingCopy());
    }

    #[Test]
    public function it_restores_a_published_entrys_working_copy_to_another_revision()
    {
        $this->setTestBlueprint('test', ['foo' => ['type' => 'text']]);
        $this->setTestRoles(['test' => ['access cp', 'edit blog entries']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $revision = tap((new Revision)
            ->key('collections/blog/en/123')
            ->date(Carbon::createFromTimestamp('1553546421', config('app.timezone')))
            ->attributes([
                'published' => false,
                'slug' => 'existing-slug',
                'date' => 1246665600, // 2009-07-04
                'data' => ['foo' => 'existing foo'],
            ]))->save();

        WorkingCopy::fromRevision($revision)->save();

        $entry = EntryFactory::id('123')
            ->slug('test')
            ->collection('blog')
            ->published(true)
            ->date('2010-12-25')
            ->data([
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'bar',
            ])->create();

        $workingCopy = tap($entry->makeWorkingCopy(), function ($copy) {
            $attrs = $copy->attributes();
            $attrs['data']['foo'] = 'foo modified in working copy';
            $attrs['date'] = 1482624000; // 2016-12-25
            $copy->attributes($attrs);
        });
        $workingCopy->save();

        $this->assertTrue($entry->published());
        $this->assertCount(1, $entry->revisions());
        $this->assertEquals('bar', $entry->get('foo'));
        $this->assertEquals('foo modified in working copy', $entry->fromWorkingCopy()->get('foo'));
        $this->assertEquals('2010-12-25', $entry->date()->format('Y-m-d'));
        $this->assertEquals('2016-12-25', $entry->fromWorkingCopy()->date()->format('Y-m-d'));

        $this
            ->actingAs($user)
            ->restore($entry, ['revision' => '1553546421'])
            ->assertOk()
            ->assertSessionHas('success');

        $entry = Entry::find($entry->id());
        $this->assertEquals('test', $entry->slug());
        $this->assertEquals('bar', $entry->get('foo'));
        $this->assertEquals('existing foo', $entry->fromWorkingCopy()->get('foo'));
        $this->assertEquals('2010-12-25', $entry->date()->format('Y-m-d'));
        $this->assertEquals('2009-07-04', $entry->fromWorkingCopy()->date()->format('Y-m-d'));
        $this->assertTrue($entry->published());
        $this->assertTrue($entry->hasWorkingCopy());
        $this->assertCount(1, $entry->revisions());
    }

    #[Test]
    public function it_restores_an_unpublished_entrys_contents_to_another_revision()
    {
        $this->setTestBlueprint('test', ['foo' => ['type' => 'text']]);
        $this->setTestRoles(['test' => ['access cp', 'edit blog entries']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $revision = tap((new Revision)
            ->key('collections/blog/en/123')
            ->date(Carbon::createFromTimestamp('1553546421', config('app.timezone')))
            ->attributes([
                'published' => true,
                'slug' => 'existing-slug',
                'data' => ['foo' => 'existing foo'],
            ]))->save();

        WorkingCopy::fromRevision($revision)->save();

        $entry = EntryFactory::id('123')
            ->slug('test')
            ->collection('blog')
            ->published(false)
            ->data([
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'bar',
            ])->create();

        $this->assertFalse($entry->published());
        $this->assertCount(1, $entry->revisions());
        $this->assertEquals('bar', $entry->get('foo'));

        $this
            ->actingAs($user)
            ->restore($entry, ['revision' => '1553546421'])
            ->assertOk()
            ->assertSessionHas('success');

        $entry = Entry::find($entry->id());
        $this->assertEquals('existing-slug', $entry->slug());
        $this->assertEquals('existing foo', $entry->get('foo'));
        $this->assertFalse($entry->published()); // everything except publish state gets restored
        $this->assertCount(1, $entry->revisions());
    }

    private function publish($entry, $payload)
    {
        return $this->post($entry->publishUrl(), $payload);
    }

    private function unpublish($entry, $payload)
    {
        return $this->post($entry->unpublishUrl(), $payload);
    }

    private function restore($entry, $payload)
    {
        return $this->post($entry->restoreRevisionUrl(), $payload);
    }

    private function setTestBlueprint($handle, $fields)
    {
        $blueprint = Blueprint::makeFromFields($fields)->setHandle($handle);

        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('find')->with('test')->andReturn($blueprint);
        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect(['test' => $blueprint]));
    }
}
