<?php

namespace Tests\Feature\Entries;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Carbon;
use Mockery;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Folder;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Fields;
use Statamic\Revisions\Revision;
use Statamic\Revisions\WorkingCopy;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EntryRevisionsTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();
        $this->dir = __DIR__.'/tmp';
        config(['statamic.revisions.enabled' => true]);
        config(['statamic.revisions.path' => $this->dir]);
        $this->setTestUserBlueprint();
        $this->collection = Collection::make('blog')->revisionsEnabled(true)->save();
    }

    public function tearDown(): void
    {
        Folder::delete($this->dir);
        parent::tearDown();
    }

    /** @test */
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
        $revision = $entry->latestRevision();
        $this->assertEquals([
            'published' => true,
            'slug' => 'test',
            'id' => '1',
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

    /** @test */
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

    /** @test */
    public function it_creates_a_revision()
    {
        $this->setTestBlueprint('test', ['foo' => ['type' => 'text']]);
        $this->setTestRoles(['test' => ['access cp', 'edit blog entries']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $entry = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->published(false)
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
            ->post($entry->createRevisionUrl(), ['message' => 'Test!'])
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
            'data' => [
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'foo modified in working copy',
            ],
        ], $revision->attributes());
        $this->assertEquals('user-1', $revision->user()->id());
        $this->assertEquals('Test!', $revision->message());
        $this->assertEquals('revision', $revision->action());
        $this->assertTrue($entry->hasWorkingCopy());
    }

    /** @test */
    public function it_restores_a_published_entrys_working_copy_to_another_revision()
    {
        $this->setTestBlueprint('test', ['foo' => ['type' => 'text']]);
        $this->setTestRoles(['test' => ['access cp', 'edit blog entries']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $revision = tap((new Revision)
            ->key('collections/blog/en/123')
            ->date(Carbon::createFromTimestamp('1553546421'))
            ->attributes([
                'published' => false,
                'slug' => 'existing-slug',
                'data' => ['foo' => 'existing foo'],
            ]))->save();

        WorkingCopy::fromRevision($revision)->save();

        $entry = EntryFactory::id('123')
            ->slug('test')
            ->collection('blog')
            ->published(true)
            ->data([
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'bar',
            ])->create();

        $workingCopy = tap($entry->makeWorkingCopy(), function ($copy) {
            $attrs = $copy->attributes();
            $attrs['data']['foo'] = 'foo modified in working copy';
            $copy->attributes($attrs);
        });
        $workingCopy->save();

        $this->assertTrue($entry->published());
        $this->assertCount(1, $entry->revisions());
        $this->assertEquals('bar', $entry->get('foo'));
        $this->assertEquals('foo modified in working copy', $entry->fromWorkingCopy()->get('foo'));

        $this
            ->actingAs($user)
            ->restore($entry, ['revision' => '1553546421'])
            ->assertOk()
            ->assertSessionHas('success');

        $entry = Entry::find($entry->id());
        $this->assertEquals('test', $entry->slug());
        $this->assertEquals('bar', $entry->get('foo'));
        $this->assertEquals('existing foo', $entry->fromWorkingCopy()->get('foo'));
        $this->assertTrue($entry->published());
        $this->assertTrue($entry->hasWorkingCopy());
        $this->assertCount(1, $entry->revisions());
    }

    /** @test */
    public function it_restores_an_unpublished_entrys_contents_to_another_revision()
    {
        $this->setTestBlueprint('test', ['foo' => ['type' => 'text']]);
        $this->setTestRoles(['test' => ['access cp', 'edit blog entries']]);
        $user = User::make()->id('user-1')->assignRole('test')->save();

        $revision = tap((new Revision)
            ->key('collections/blog/en/123')
            ->date(Carbon::createFromTimestamp('1553546421'))
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
        $fields = collect($fields)->map(function ($field, $handle) {
            return compact('handle', 'field');
        })->all();

        $blueprint = Mockery::mock(Blueprint::class);
        $blueprint->shouldReceive('fields')->andReturn(new Fields($fields));

        $blueprint->shouldReceive('ensureField')->andReturnSelf();
        $blueprint->shouldReceive('ensureFieldPrepended')->andReturnSelf();

        BlueprintRepository::shouldReceive('find')->with('test')->andReturn($blueprint);
    }

    private function setTestUserBlueprint()
    {
        $blueprint = \Statamic\Facades\Blueprint::find('user');
        BlueprintRepository::shouldReceive('find')->with('user')->andReturn($blueprint);
    }
}
