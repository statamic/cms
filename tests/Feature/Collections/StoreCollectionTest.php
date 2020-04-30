<?php

namespace Tests\Feature\Collections;

use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StoreCollectionTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();
        $this->markTestIncomplete(); // TODO: implementation was changed, tests werent.
    }

    /** @test */
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this
            ->from('/original')
            ->actingAs($this->userWithoutPermission())
            ->post(cp_route('collections.store'))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    /** @test */
    public function it_stores_a_collection()
    {
        $this->assertCount(0, Collection::all());

        $this
            ->actingAs($this->userWithPermission())
            ->post(cp_route('collections.store'), $this->validParams())
            ->assertJson(['redirect' => cp_route('collections.show', 'test')])
            ->assertSessionHas('success');

        $this->assertCount(1, Collection::all());
        $collection = Collection::all()->first();
        $this->assertEquals('test', $collection->handle());
        $this->assertEquals([
            'title' => 'Test Collection',
            'template' => 'test-template',
            'fieldset' => 'test-fieldset',
            'route' => 'test-route',
            'order' => 'number',
        ], $collection->data());
    }

    /** @test */
    public function title_is_required()
    {
        $this->assertCount(0, Collection::all());

        $this
            ->from('/original')
            ->actingAs($this->userWithPermission())
            ->post(cp_route('collections.store'), $this->validParams([
                'title' => '',
            ]))
            ->assertRedirect('/original')
            ->assertSessionHasErrors('title');

        $this->assertCount(0, Collection::all());
    }

    /** @test */
    public function handle_must_be_alpha_dash()
    {
        $this->assertCount(0, Collection::all());

        $this
            ->from('/original')
            ->actingAs($this->userWithPermission())
            ->post(cp_route('collections.store'), $this->validParams([
                'handle' => 'there are spaces in here',
            ]))
            ->assertRedirect('/original')
            ->assertSessionHasErrors('handle');

        $this->assertCount(0, Collection::all());
    }

    /** @test */
    public function handle_is_a_slugified_title_if_not_provided()
    {
        $this->assertCount(0, Collection::all());

        $this
            ->from('/original')
            ->actingAs($this->userWithPermission())
            ->post(cp_route('collections.store'), $this->validParams([
                'title' => 'An Example Collection',
                'handle' => '',
            ]));

        $this->assertCount(1, Collection::all());
        $collection = Collection::all()->first();
        $this->assertEquals('an_example_collection', $collection->handle());
    }

    private function validParams($overrides = [])
    {
        return array_merge([
            'title' => 'Test Collection',
            'handle' => 'test',
            'template' => 'test-template',
            'fieldset' => 'test-fieldset',
            'route' => 'test-route',
            'order' => 'number',
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
