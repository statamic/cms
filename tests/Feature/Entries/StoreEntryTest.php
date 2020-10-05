<?php

namespace Tests\Feature\Entries;

use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Support\Carbon;
use Mockery;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Folder;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Fields;
use Statamic\Testing\FakesRoles;
use Statamic\Testing\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StoreEntryTest extends TestCase
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
        Collection::make('blog')->sites(['en'])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->store([])
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    /** @test */
    public function entry_gets_created()
    {
        $now = Carbon::parse('2017-02-03');
        Carbon::setTestNow($now);
        $this->setTestBlueprint('test', ['title' => ['type' => 'text'], 'foo' => ['type' => 'text']]);
        $this->setTestRoles(['test' => ['access cp', 'create blog entries']]);
        $user = User::make()->assignRole('test');
        Collection::make('blog')->sites(['en'])->save();
        $this->assertCount(0, Entry::all());

        $response = $this
            ->actingAs($user)
            ->store([
                'blueprint' => 'test',
                'title' => 'The title',
                'slug' => 'the-slug',
                'foo' => 'bar',
            ])
            ->assertOk();

        $this->assertCount(1, Entry::all());
        $entry = Entry::all()->first();

        $response->assertJson([
            'redirect' => "http://localhost/cp/collections/blog/entries/{$entry->id()}/the-slug/en",
            'entry' => [],
        ]);

        $this->assertEquals('the-slug', $entry->slug());
        $this->assertEquals([
            'title' => 'The title',
            'foo' => 'bar',
            'updated_at' => $now->timestamp,
            'updated_by' => $user->id(),
        ], $entry->data());
        $this->assertFalse($entry->published());
        $this->assertCount(1, $entry->revisions());
        $this->assertEquals('revision', $entry->latestRevision()->action());
    }

    /** @test */
    public function validation_error_returns_back()
    {
        $this->setTestBlueprint('test', ['title' => ['type' => 'text'], 'foo' => ['type' => 'text', 'validate' => 'required']]);
        $this->setTestRoles(['test' => ['access cp', 'create blog entries']]);
        $user = User::make()->assignRole('test');
        Collection::make('blog')->sites(['en'])->save();
        $this->assertCount(0, Entry::all());

        $this
            ->from('/original')
            ->actingAs($user)
            ->store([
                'blueprint' => 'test',
                'title' => 'The title',
                'slug' => 'the-slug',
                'foo' => '',
            ])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('foo');

        $this->assertCount(0, Entry::all());
    }

    private function store($payload)
    {
        return $this->post(cp_route('collections.entries.store', ['blog', 'en']), $payload);
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
