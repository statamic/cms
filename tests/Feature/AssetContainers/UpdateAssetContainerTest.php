<?php

namespace Tests\Feature\AssetContainers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateAssetContainerTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();
        $container = AssetContainer::make('test')->title('Original Title')->save();

        $this
            ->actingAs($user)
            ->update($container, ['title' => 'Updated Title'])
            ->assertForbidden();

        $this->assertEquals('Original Title', AssetContainer::find('test')->title());
    }

    #[Test]
    public function it_updates_a_container()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure asset containers']]);
        $user = User::make()->assignRole('test')->save();
        $container = AssetContainer::make('test')->title('Original Title')->save();

        $this
            ->actingAs($user)
            ->update($container, ['title' => 'Updated Title'])
            ->assertSuccessful();

        $this->assertEquals('Updated Title', AssetContainer::find('test')->title());
    }

    #[Test]
    public function it_fails_validation_without_required_fields()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure asset containers']]);
        $user = User::make()->assignRole('test')->save();
        $container = AssetContainer::make('test')->title('Original Title')->save();

        $this
            ->actingAs($user)
            ->update($container, ['title' => '', 'disk' => ''])
            ->assertJsonValidationErrors([
                'title' => trans('statamic::validation.required'),
                'disk' => trans('statamic::validation.required'),
            ]);

        $this->assertEquals('Original Title', AssetContainer::find('test')->title());
    }

    private function update($container, $payload = [])
    {
        return $this->json(
            'PATCH',
            cp_route('asset-containers.update', $container->handle()),
            array_merge([
                'title' => 'Title',
                'disk' => 'local',
            ], $payload)
        );
    }
}
