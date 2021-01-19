<?php

namespace Tests\Feature\Entries;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Carbon;
use Mockery;
use Statamic\Facades\Entry;
use Statamic\Facades\Folder;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Fields;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateEntryTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        $this->markTestIncomplete('Needs to be updated for localization and when revisions are enabled/disabled.');
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
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test');

        $entry = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->data(['blueprint' => 'test'])
            ->create();

        $this
            ->from('/original')
            ->actingAs($user)
            ->save($entry, [])
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    /** @test */
    public function published_entry_gets_saved_to_working_copy()
    {
        $now = Carbon::parse('2017-02-03');
        Carbon::setTestNow($now);
        $this->setTestBlueprint('test', ['foo' => ['type' => 'text']]);
        $this->setTestRoles(['test' => ['access cp', 'edit blog entries']]);
        $user = User::make()->assignRole('test');

        $entry = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->data([
                'blueprint' => 'test',
                'title' => 'Original title',
                'foo' => 'bar',
                'updated_at' => $originalTimestamp = $now->subDays(3)->timestamp,
            ])->create();

        $this
            ->actingAs($user)
            ->save($entry, [
                'title' => 'Updated title',
                'foo' => 'updated foo',
                'slug' => 'updated-slug',
            ])
            ->assertOk();

        $entry = Entry::find($entry->id());
        $this->assertEquals('test', $entry->slug());
        $this->assertEquals([
            'blueprint' => 'test',
            'title' => 'Original title',
            'foo' => 'bar',
            'updated_at' => $originalTimestamp,
        ], $entry->data());

        $workingCopy = $entry->fromWorkingCopy();
        $this->assertEquals('updated-slug', $workingCopy->slug());
        $this->assertEquals([
            'blueprint' => 'test',
            'title' => 'Updated title',
            'foo' => 'updated foo',
        ], $workingCopy->data());
    }

    /** @test */
    public function draft_entry_gets_saved_to_content()
    {
        $now = Carbon::parse('2017-02-03');
        Carbon::setTestNow($now);
        $this->setTestBlueprint('test', ['foo' => ['type' => 'text']]);
        $this->setTestRoles(['test' => ['access cp', 'edit blog entries']]);
        $user = User::make()->assignRole('test');

        $entry = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->published(false)
            ->data([
                'blueprint' => 'test',
                'title' => 'Original title',
                'foo' => 'bar',
                'updated_at' => $now->subDays(2)->timestamp,
                'updated_by' => $user->id(),
            ])->create();

        $this
            ->actingAs($user)
            ->save($entry, [
                'title' => 'Updated title',
                'foo' => 'updated foo',
                'slug' => 'updated-slug',
            ])
            ->assertOk();

        $entry = Entry::find($entry->id());
        $this->assertEquals('updated-slug', $entry->slug());
        $this->assertEquals([
            'blueprint' => 'test',
            'title' => 'Updated title',
            'foo' => 'updated foo',
            'updated_at' => $now->timestamp,
            'updated_by' => $user->id(),
        ], $entry->data());
        $this->assertFalse($entry->hasWorkingCopy());
    }

    /** @test */
    public function validation_error_returns_back()
    {
        $this->setTestBlueprint('test', ['foo' => ['type' => 'text', 'validate' => 'required']]);
        $this->setTestRoles(['test' => ['access cp', 'edit blog entries']]);
        $user = User::make()->assignRole('test');

        $entry = EntryFactory::id('1')
            ->slug('test')
            ->collection('blog')
            ->data([
                'blueprint' => 'test',
                'title' => 'Original title',
                'foo' => 'bar',
            ])->create();

        $this
            ->from('/original')
            ->actingAs($user)
            ->save($entry, [
                'title' => 'Updated title',
                'foo' => '',
                'slug' => 'updated-slug',
            ])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('foo');

        $this->assertEquals('test', $entry->slug());
        $this->assertEquals([
            'blueprint' => 'test',
            'title' => 'Original title',
            'foo' => 'bar',
        ], $entry->data());
    }

    /** @test */
    public function user_without_permission_to_manage_publish_state_cannot_change_publish_status()
    {
        // when revisions are disabled

        $this->markTestIncomplete();
    }

    private function save($entry, $payload)
    {
        return $this->patch($entry->updateUrl(), $payload);
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
