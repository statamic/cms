<?php

namespace Tests\Feature\Collections\Blueprints;

use Facades\Statamic\Fields\BlueprintRepository;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Facades\Collection;
use Tests\Fakes\FakeBlueprintRepository;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StoreBlueprintTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        BlueprintRepository::swap(new FakeBlueprintRepository(BlueprintRepository::getFacadeRoot()));
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test'))->save();
        $this->assertCount(0, Facades\Blueprint::in('collections/test'));

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit($collection)
            ->assertRedirect('/original')
            ->assertSessionHas('error');

        $this->assertCount(0, Facades\Blueprint::in('collections/test'));
    }

    #[Test]
    public function blueprint_gets_created()
    {
        $this->withoutExceptionHandling();
        $this->setTestRoles(['test' => ['access cp', 'configure fields']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test'))->save();

        Facades\Blueprint::make('existing')->setNamespace('collections.test')->save();
        $this->assertCount(1, Facades\Blueprint::in('collections/test'));

        $this
            ->actingAs($user)
            ->submit($collection, ['title' => 'My Test Blueprint'])
            ->assertJson([
                'redirect' => 'http://localhost/cp/fields/blueprints/collections/test/my_test_blueprint/edit',
            ]);

        $this->assertCount(2, Facades\Blueprint::in('collections/test'));
        $blueprint = Facades\Blueprint::in('collections/test')->last();
        $this->assertEquals('my_test_blueprint', $blueprint->handle());
        $this->assertEquals([
            'title' => 'My Test Blueprint',
            'tabs' => [
                'main' => [
                    'display' => 'Main',
                    'fields' => [],
                ],
            ],
        ], $blueprint->contents());
    }

    #[Test]
    public function when_creating_the_first_blueprint_the_default_one_is_also_created()
    {
        // If there are no user-defined blueprints, save the default one.
        // To the user, it would have looked like the default one existed since it's in the listing.
        // The new one the user is about to create should be considered the second one.
        $this->setTestRoles(['test' => ['access cp', 'configure fields']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test_collection'))->save();

        $this->assertCount(0, Facades\Blueprint::in('collections/test_collection'));

        $this
            ->actingAs($user)
            ->submit($collection, ['title' => 'My Test Blueprint'])
            ->assertJson([
                'redirect' => 'http://localhost/cp/fields/blueprints/collections/test_collection/my_test_blueprint/edit',
            ]);

        $this->assertCount(2, Facades\Blueprint::in('collections/test_collection'));

        $this->assertEquals(
            ['test_collection', 'my_test_blueprint'],
            Facades\Blueprint::in('collections/test_collection')->map->handle()->values()->all()
        );
    }

    #[Test]
    public function title_is_required()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure fields']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test'))->save();
        $this->assertCount(0, Facades\Blueprint::in('collections/test'));

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit($collection, ['title' => ''])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('title');

        $this->assertCount(0, Facades\Blueprint::in('collections/test'));
    }

    private function submit($collection, $params = [])
    {
        return $this->post(cp_route('blueprints.collections.store', $collection), $this->validParams($params));
    }

    private function validParams($overrides = [])
    {
        return array_merge([
            'title' => 'Test',
        ], $overrides);
    }
}
