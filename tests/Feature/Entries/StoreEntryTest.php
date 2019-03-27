<?php

namespace Tests\Feature\Entries;

use Mockery;
use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\API\User;
use Statamic\API\Entry;
use Statamic\API\Folder;
use Statamic\Fields\Fields;
use Statamic\API\Collection;
use Statamic\Fields\Blueprint;
use Tests\PreventSavingStacheItemsToDisk;
use Facades\Statamic\Fields\BlueprintRepository;

class StoreEntryTest extends TestCase
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
    function it_denies_access_if_you_dont_have_permission()
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
    function entry_gets_created()
    {
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
            'entry' => []
        ]);

        $this->assertEquals('the-slug', $entry->slug());
        $this->assertEquals([
            'title' => 'The title',
            'foo' => 'bar',
        ], $entry->data());
        $this->assertFalse($entry->published());
        $this->assertCount(1, $entry->revisions());
    }

    /** @test */
    function validation_error_returns_back()
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
