<?php

namespace Tests\Feature\Entries;

use Mockery;
use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\API\User;
use Statamic\API\Entry;
use Statamic\API\Folder;
use Statamic\Fields\Fields;
use Statamic\Fields\Blueprint;
use Facades\Tests\Factories\EntryFactory;
use Tests\PreventSavingStacheItemsToDisk;
use Facades\Statamic\Fields\BlueprintRepository;

class PublishEntryTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();
        $this->dir = __DIR__.'/tmp';
        config(['statamic.revisions.path' => $this->dir]);
    }

    public function tearDown(): void
    {
        Folder::delete($this->dir);
        parent::tearDown();
    }

    /** @test */
    function it_publishes_an_entry()
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

        $this->assertFalse($entry->published());
        $this->assertCount(0, $entry->revisions());

        $this
            ->actingAs($user)
            ->publish($entry, ['message' => 'Test!'])
            ->assertOk();

        $entry = Entry::find($entry->id());
        $this->assertTrue($entry->published());
        $this->assertCount(1, $entry->revisions());
        $revision = $entry->latestRevision();
        $this->assertEquals([
            'published' => true,
            'slug' => 'test',
            'data' => [
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'bar',
            ]
        ], $revision->attributes());
        $this->assertEquals('user-1', $revision->user()->id());
        $this->assertEquals('Test!', $revision->message());
    }

    /** @test */
    function it_unpublishes_an_entry()
    {
        $this->withoutExceptionHandling();
        $this->setTestBlueprint('test', ['foo' => ['type' => 'text']]);
        $this->setTestRoles(['test' => ['access cp', 'edit blog entries']]);
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
        $this->assertFalse($entry->published());
        $this->assertCount(1, $entry->revisions());
        $revision = $entry->latestRevision();
        $this->assertEquals([
            'published' => false,
            'slug' => 'test',
            'data' => [
                'blueprint' => 'test',
                'title' => 'Title',
                'foo' => 'bar',
            ]
        ], $revision->attributes());
        $this->assertEquals('user-1', $revision->user()->id());
        $this->assertEquals('Test!', $revision->message());
    }

    private function publish($entry, $payload)
    {
        return $this->post($entry->publishUrl(), $payload);
    }

    private function unpublish($entry, $payload)
    {
        return $this->delete($entry->publishUrl(), $payload);
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
}
