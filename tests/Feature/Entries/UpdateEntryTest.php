<?php

namespace Tests\Feature\Entries;

use Mockery;
use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\API\User;
use Statamic\API\Entry;
use Statamic\Fields\Fields;
use Statamic\API\Collection;
use Statamic\Fields\Blueprint;
use Tests\PreventSavingStacheItemsToDisk;
use Facades\Tests\Factories\EntryFactory;
use Facades\Statamic\Fields\BlueprintRepository;

class UpdateEntryTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
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
            ->submit($entry, [])
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    /** @test */
    function entry_gets_saved()
    {
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
            ])->create();

        $this
            ->actingAs($user)
            ->submit($entry, [
                'title' => 'Updated title',
                'foo' => 'updated foo',
                'slug' => 'updated-slug'
            ])
            ->assertOk();

        $this->assertEquals('updated-slug', $entry->slug());
        $this->assertEquals([
            'blueprint' => 'test',
            'title' => 'Updated title',
            'foo' => 'updated foo',
        ], $entry->data());
    }

    /** @test */
    function validation_error_returns_back()
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
            ->submit($entry, [
                'title' => 'Updated title',
                'foo' => '',
                'slug' => 'updated-slug'
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

    private function submit($entry, $payload)
    {
        return $this->patch(
            cp_route('collections.entries.update', [$entry->collectionHandle(), $entry->id(), $entry->slug(), 'en']),
            $payload
        );
    }

    private function setTestBlueprint($handle, $fields)
    {
        $fields = collect($fields)->map(function ($field, $handle) {
            return compact('handle', 'field');
        })->all();

        $blueprint = Mockery::mock(Blueprint::class);
        $blueprint->shouldReceive('fields')->andReturn(new Fields($fields));

        BlueprintRepository::shouldReceive('find')->with('test')->andReturn($blueprint);
    }
}
