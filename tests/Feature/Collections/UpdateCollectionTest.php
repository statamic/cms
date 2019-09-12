<?php

namespace Tests\Feature\Collections;

use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\Facades\User;
use Statamic\Facades\Collection;
use Illuminate\Support\Facades\Event;
use Statamic\Events\Data\CollectionSaved;
use Tests\PreventSavingStacheItemsToDisk;

class UpdateCollectionTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();
        $this->markTestIncomplete(); // TODO: implementation was changed, tests werent.
    }

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
    {
        $collection = Collection::make('test')->save();

        $this
            ->from('/original')
            ->actingAs($this->userWithoutPermission())
            ->patch(cp_route('collections.update', $collection->handle()))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    /** @test */
    function it_updates_a_collection()
    {
        $collection = $this->createCollection();
        Event::fake(); // Fake after the initial collection is created.
        $this->assertCount(1, Collection::all());

        $this
            ->actingAs($this->userWithPermission())
            ->patch(cp_route('collections.update', $collection->handle()), $this->validParams())
            ->assertJson(['redirect' => cp_route('collections.edit', $collection->handle())])
            ->assertSessionHas('success');

        $this->assertCount(1, Collection::all());
        $this->assertEquals([
            'title' => 'Updated',
            'template' => 'updated-template',
            'fieldset' => 'updated-fieldset',
            'route' => 'updated-route',
            'order' => 'number'
        ], $collection->data());

        Event::assertDispatched(CollectionSaved::class, function ($event) use ($collection) {
            return $event->collection === $collection;
        });
    }

    /** @test */
    function title_is_required()
    {
        $collection = $this->createCollection();
        $this->assertCount(1, Collection::all());

        $this
            ->from('/original')
            ->actingAs($this->userWithPermission())
            ->patch(cp_route('collections.update', $collection->handle()), $this->validParams([
                'title' => ''
            ]))
            ->assertRedirect('/original')
            ->assertSessionHasErrors('title');

        $this->assertCollectionUnchanged($collection);
    }

    private function createCollection()
    {
        return tap(Collection::make('test'))->data([
            'title' => 'Existing',
            'template' => 'existing-template',
            'fieldset' => 'existing-fieldset',
            'route' => 'existing-route',
            'order' => 'number',
        ])->save();
    }

    private function validParams($overrides = [])
    {
        return array_merge([
            'title' => 'Updated',
            'template' => 'updated-template',
            'fieldset' => 'updated-fieldset',
            'route' => 'updated-route',
        ], $overrides);
    }

    private function userWithoutPermission()
    {
        $this->setTestRoles(['test' => ['access cp']]);

        return User::make()->assignRole('test');
    }

    private function userWithPermission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure collections']]);

        return User::make()->assignRole('test');
    }

    private function assertCollectionUnchanged($collection)
    {
        $this->assertEquals('Existing', $collection->get('title'));
        $this->assertEquals('existing-template', $collection->get('template'));
        $this->assertEquals('existing-fieldset', $collection->get('fieldset'));
        $this->assertEquals('existing-route', $collection->get('route'));
    }
}
