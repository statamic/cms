<?php

namespace Tests\Feature\Blueprints;

use Facades\Statamic\Fields\BlueprintRepository;
use Statamic\Facades;
use Tests\Fakes\FakeBlueprintRepository;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CreateBlueprintTest extends TestCase
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

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('blueprints.create'))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }
}
