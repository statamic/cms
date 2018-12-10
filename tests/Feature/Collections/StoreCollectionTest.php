<?php

namespace Tests\Feature\Collections;

use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\API\User;
use Statamic\API\Collection;
use Tests\PreventSavingStacheItemsToDisk;

class StoreCollectionTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
    {
        $this
            ->from('/original')
            ->actingAs($this->userWithoutPermission())
            ->post(cp_route('collections.store'))
            ->assertRedirect('/original')
            ->assertSessionHasErrors();
    }

    /** @test */
    function it_stores_a_collection()
    {
        $this->assertCount(0, Collection::all());

        $this
            ->actingAs($this->userWithPermission())
            ->post(cp_route('collections.store'), $this->validParams())
            ->assertRedirect(cp_route('collections.edit', 'test'))
            ->assertSessionHas('success');

        $this->assertCount(1, Collection::all());
        $collection = Collection::all()->first();
        $this->assertEquals('test', $collection->path());
        $this->assertEquals([
            'title' => 'Test Collection',
            'template' => 'test-template',
            'fieldset' => 'test-fieldset',
            'route' => 'test-route',
            'order' => 'number',
        ], $collection->data());
    }

    /** @test */
    function title_is_required()
    {
        $this->assertCount(0, Collection::all());

        $this
            ->from('/original')
            ->actingAs($this->userWithPermission())
            ->post(cp_route('collections.store'), $this->validParams([
                'title' => ''
            ]))
            ->assertRedirect('/original')
            ->assertSessionHasErrors('title');

        $this->assertCount(0, Collection::all());
    }

    /** @test */
    function handle_must_be_alpha_dash()
    {
        $this->assertCount(0, Collection::all());

        $this
            ->from('/original')
            ->actingAs($this->userWithPermission())
            ->post(cp_route('collections.store'), $this->validParams([
                'handle' => 'there are spaces in here'
            ]))
            ->assertRedirect('/original')
            ->assertSessionHasErrors('handle');

        $this->assertCount(0, Collection::all());
    }

    /** @test */
    function handle_is_a_slugified_title_if_not_provided()
    {
        $this->assertCount(0, Collection::all());

        $this
            ->from('/original')
            ->actingAs($this->userWithPermission())
            ->post(cp_route('collections.store'), $this->validParams([
                'title' => 'An Example Collection',
                'handle' => ''
            ]));

        $this->assertCount(1, Collection::all());
        $collection = Collection::all()->first();
        $this->assertEquals('an_example_collection', $collection->path());
    }

    private function validParams($overrides = [])
    {
        return array_merge([
            'title' => 'Test Collection',
            'handle' => 'test',
            'template' => 'test-template',
            'fieldset' => 'test-fieldset',
            'route' => 'test-route',
            'order' => 'number'
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
}
