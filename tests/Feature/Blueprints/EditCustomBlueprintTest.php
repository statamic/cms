<?php

namespace Tests\Feature\Blueprints;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Fields\Blueprint;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EditCustomBlueprintTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();

        $namespace = 'foo';
        $handle = 'bar';

        Facades\Blueprint::addNamespace($namespace, 'resources/content/'.$namespace);

        $blueprint = $this->createBlueprint($namespace, $handle);
        $blueprint->save();

        $this
            ->actingAs($user)
            ->get(cp_route('blueprints.edit', [$namespace, $handle]))
            ->assertRedirect('/cp')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_lets_you_edit_a_custom_namespace_blueprint()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure fields']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();

        $namespace = 'foo';
        $handle = 'bar';

        Facades\Blueprint::addNamespace($namespace, 'resources/content/'.$namespace);

        $blueprint = $this->createBlueprint($namespace, $handle);
        $blueprint->save();

        $this
            ->actingAs($user)
            ->get(cp_route('blueprints.edit', [$namespace, $handle]))
            ->assertOk()
            ->assertViewIs('statamic::blueprints.edit');
    }

    private function createBlueprint($namespace, $handle)
    {
        return tap(new Blueprint)->setHandle($handle)->setNamespace($namespace);
    }
}
