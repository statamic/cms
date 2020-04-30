<?php

namespace Tests\Feature\Blueprints;

use Facades\Statamic\Fields\BlueprintRepository;
use Statamic\Facades;
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

        BlueprintRepository::swap(new FakeBlueprintRepository);
    }

    /** @test */
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        $this->assertCount(0, Facades\Blueprint::all());

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit()
            ->assertRedirect('/original')
            ->assertSessionHas('error');

        $this->assertCount(0, Facades\Blueprint::all());
    }

    /** @test */
    public function blueprint_gets_saved()
    {
        $user = tap(Facades\User::make()->makeSuper())->save();
        $this->assertCount(0, Facades\Blueprint::all());

        $this
            ->actingAs($user)
            ->submit(['title' => 'My Test Blueprint'])
            ->assertRedirect('/cp/fields/blueprints/my_test_blueprint/edit');

        $this->assertCount(1, Facades\Blueprint::all());
        $blueprint = Facades\Blueprint::all()->first();
        $this->assertEquals('my_test_blueprint', $blueprint->handle());
        $this->assertEquals([
            'title' => 'My Test Blueprint',
            'sections' => [
                'main' => [
                    'display' => 'Main',
                    'fields' => [],
                ],
            ],
        ], $blueprint->contents());
    }

    /** @test */
    public function title_is_required()
    {
        $user = tap(Facades\User::make()->makeSuper())->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit(['title' => ''])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('title');

        $this->assertCount(0, Facades\Blueprint::all());
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
