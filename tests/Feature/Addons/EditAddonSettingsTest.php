<?php

namespace Feature\Addons;

use Foo\Bar\TestAddonServiceProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Extend\Addon;
use Statamic\Facades;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EditAddonSettingsTest extends TestCase
{
    use FakesRoles, PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        $addon = $this->makeFromPackage(['slug' => 'test-addon']);

        Facades\Addon::shouldReceive('all')->andReturn(collect([$addon]));
        Facades\Addon::shouldReceive('get')->with('vendor/test-addon')->andReturn($addon);

        $this->app->bind('statamic.addons.test-addon.settings_blueprint', fn () => [
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'api_key',
                                    'field' => ['type' => 'text'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    #[Test]
    public function can_edit_addon_settings()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('addons.settings.edit', 'test-addon'))
            ->assertOk()
            ->assertSee('Test Addon');
    }

    #[Test]
    public function can_edit_addon_settings_with_configure_addons_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure addons']]);

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->get(cp_route('addons.settings.edit', 'test-addon'))
            ->assertOk()
            ->assertSee('Test Addon');
    }

    #[Test]
    public function can_edit_addon_settings_with_edit_addon_settings_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit vendor/test-addon settings']]);

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->get(cp_route('addons.settings.edit', 'test-addon'))
            ->assertOk()
            ->assertSee('Test Addon');
    }

    #[Test]
    public function cant_edit_addon_settings_without_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);

        $this
            ->actingAs(User::make()->save())
            ->get(cp_route('addons.settings.edit', 'test-addon'))
            ->assertRedirect('/cp');
    }

    #[Test]
    public function cant_edit_addon_settings_for_non_existent_addon()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('addons.settings.edit', 'non-existent-addon'))
            ->assertNotFound();
    }

    #[Test]
    public function cant_edit_addon_settings_when_addon_doesnt_have_any_settings()
    {
        // Forget the settings blueprint from the container.
        $this->app->offsetUnset('statamic.addons.test-addon.settings_blueprint');

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('addons.settings.edit', 'test-addon'))
            ->assertNotFound();
    }

    private function makeFromPackage($attributes = [])
    {
        return Addon::makeFromPackage(array_merge([
            'id' => 'vendor/test-addon',
            'name' => 'Test Addon',
            'description' => 'Test description',
            'namespace' => 'Vendor\\TestAddon',
            'provider' => TestAddonServiceProvider::class,
            'autoload' => '',
            'url' => 'http://test-url.com',
            'developer' => 'Test Developer LLC',
            'developerUrl' => 'http://test-developer.com',
            'version' => '1.0',
            'editions' => ['foo', 'bar'],
        ], $attributes));
    }
}
