<?php

namespace Tests\Feature\Collections;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StoreCollectionTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this
            ->from('/original')
            ->actingAs($this->userWithoutPermission())
            ->post(cp_route('collections.store'))
            ->assertRedirect('/original')
            ->assertSessionHas('error', 'You are not authorized to create collections.');
    }

    #[Test]
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
        $this->assertEquals('Test Collection', $collection->title());
        $this->assertEquals('public', $collection->pastDateBehavior());
        $this->assertEquals('private', $collection->futureDateBehavior());
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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
        ], $overrides);
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
}
