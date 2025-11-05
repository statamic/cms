<?php

namespace Tests\Feature\Blueprints;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Fields\Blueprint;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StoreCustomBlueprintTest extends TestCase
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
            ->submit($namespace, $handle, [
                'title' => 'test',
                'handle' => 'bar',
            ])
            ->assertRedirect('/cp')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_lets_you_update_a_custom_namespace_blueprint()
    {
        $this->withoutExceptionHandling();

        $this->setTestRoles(['test' => ['access cp', 'configure fields']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();

        $namespace = 'foo';
        $handle = 'bar';

        Facades\Blueprint::addNamespace($namespace, 'resources/content/'.$namespace);

        $blueprint = $this->createBlueprint($namespace, $handle);
        $blueprint->save();

        $this
            ->actingAs($user)
            ->submit($namespace, $handle, [
                'title' => 'test',
                'handle' => 'bar',
            ])
            ->assertOk();
    }

    private function createBlueprint($namespace, $handle)
    {
        $blueprint = tap(new Blueprint)->setHandle($handle)->setNamespace($namespace);
        $blueprint->save();

        return $blueprint;
    }

    private function submit($namespace, $blueprint, $params = [])
    {
        return $this->patch(
            cp_route('blueprints.additional.update', [$namespace, $blueprint]),
            $this->validParams($params)
        );
    }

    private function validParams($overrides = [])
    {
        return array_merge([
            'title' => 'Updated',
            'tabs' => [],
        ], $overrides);
    }
}
