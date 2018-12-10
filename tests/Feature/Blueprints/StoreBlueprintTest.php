<?php

namespace Tests\Feature\Blueprints;

use Statamic\API;
use Tests\TestCase;
use Tests\FakesRoles;
use Tests\Fakes\FakeBlueprintRepository;
use Facades\Statamic\Fields\BlueprintRepository;

class StoreBlueprintTest extends TestCase
{
    use FakesRoles;

    protected function setUp()
    {
        parent::setUp();

        BlueprintRepository::swap(new FakeBlueprintRepository);
    }

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = API\User::make()->assignRole('test');
        $this->assertCount(0, API\Blueprint::all());

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit()
            ->assertRedirect('/original')
            ->assertSessionHasErrors();

        $this->assertCount(0, API\Blueprint::all());
    }

    /** @test */
    function blueprint_gets_saved()
    {
        $user = API\User::make()->makeSuper();
        $this->assertCount(0, API\Blueprint::all());

        $this
            ->actingAs($user)
            ->submit(['title' => 'My Test Blueprint'])
            ->assertRedirect('/cp/blueprints/my_test_blueprint/edit');

        $this->assertCount(1, API\Blueprint::all());
        $blueprint = API\Blueprint::all()->first();
        $this->assertEquals('my_test_blueprint', $blueprint->handle());
        $this->assertEquals([
            'title' => 'My Test Blueprint',
            'sections' => []
        ], $blueprint->contents());
    }

    /** @test */
    function title_is_required()
    {
        $user = API\User::make()->makeSuper();

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit(['title' => ''])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('title');

        $this->assertCount(0, API\Blueprint::all());
    }

    private function submit($params = [])
    {
        return $this->post(cp_route('blueprints.store'), $this->validParams($params));
    }

    private function validParams($overrides = [])
    {
        return array_merge([
            'title' => 'Test',
        ], $overrides);
    }
}
