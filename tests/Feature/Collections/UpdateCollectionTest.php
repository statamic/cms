<?php

namespace Tests\Feature\Collections;

use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Statamic\Fields\BlueprintRepository;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateCollectionTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
    {
        $collection = Collection::make('test')->save();

        $this
            ->from('/original')
            ->actingAs($this->userWithoutPermission())
            ->update($collection)
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    /** @test */
    function it_updates_a_collection()
    {
        config(['statamic.amp.enabled' => true]);

        $collection = tap(
            Collection::make('test')
            ->title('Original title')
            ->dated(false)
            ->template('original-template')
            ->layout('original-layout')
            ->defaultPublishState(true)
            ->sortDirection('asc')
            ->ampable(false)
        )->save();
        $this->assertCount(1, Collection::all());
        $this->assertEquals('Original title', $collection->title());
        $this->assertFalse($collection->dated());
        $this->assertEquals('public', $collection->pastDateBehavior());
        $this->assertEquals('public', $collection->futureDateBehavior());
        $this->assertEquals('original-template', $collection->template());
        $this->assertEquals('original-layout', $collection->layout());
        $this->assertTrue($collection->defaultPublishState());
        $this->assertEquals('asc', $collection->sortDirection());
        $this->assertFalse($collection->ampable());

        $this
            ->actingAs($this->userWithPermission())
            ->update($collection, [
                'title' => 'Updated title',
                'dated' => true,
                'past_date_behavior' => 'private',
                'future_date_behavior' => 'hidden',
                'template' => 'updated-template',
                'layout' => 'updated-layout',
                'default_publish_state' => false,
                'sort_direction' => 'desc',
                'amp' => true,
            ])
            ->assertOk();

        $this->assertCount(1, Collection::all());
        $updated = Collection::all()->first();
        $this->assertEquals('Updated title', $updated->title());
        $this->assertTrue($updated->dated());
        $this->assertEquals('private', $collection->pastDateBehavior());
        $this->assertEquals('hidden', $collection->futureDateBehavior());
        $this->assertEquals('updated-template', $updated->template());
        $this->assertEquals('updated-layout', $updated->layout());
        $this->assertFalse($updated->defaultPublishState());
        $this->assertEquals('desc', $updated->sortDirection());
        $this->assertTrue($updated->ampable());
        // $this->assertEquals(['three', 'four'], $updated->entryBlueprints());
        // structure
    }

    /** @test */
    function it_updates_blueprints()
    {
        $mock = $this->partialMock(BlueprintRepository::class);
        $mock->shouldReceive('find')->with('one')->andReturn(Blueprint::make('one'));
        $mock->shouldReceive('find')->with('two')->andReturn(Blueprint::make('two'));
        $mock->shouldReceive('find')->with('three')->andReturn(Blueprint::make('three'));
        $mock->shouldReceive('find')->with('four')->andReturn(Blueprint::make('four'));

        $collection = tap(Collection::make('test')->entryBlueprints(['one', 'two']))->save();
        $this->assertEquals(['one', 'two'], $collection->entryBlueprints()->map->handle()->all());

        $this
            ->actingAs($this->userWithPermission())
            ->update($collection, ['blueprints' => ['three', 'four']])
            ->assertOk();

        $this->assertEquals(['three', 'four'], Collection::all()->first()->entryBlueprints()->map->handle()->all());
    }

    private function userWithoutPermission()
    {
        $this->setTestRoles(['test' => ['access cp']]);

        return tap(User::make()->assignRole('test'))->save();
    }

    private function userWithPermission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure collections']]);

        return tap(User::make()->assignRole('test'))->save();
    }

    private function update($collection, $params = [])
    {
        $params = array_merge([
            'title' => 'Updated title',
            'dated' => false,
            'past_date_behavior' => 'public',
            'future_date_behavior' => 'public',
            'template' => 'updated-template',
            'layout' => 'updated-layout',
            'default_publish_state' => true,
            'ampable' => false,
        ], $params);

        return $this->patch(cp_route('collections.update', $collection->handle()), $params);
    }
}
