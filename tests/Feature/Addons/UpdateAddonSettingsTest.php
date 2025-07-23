<?php

namespace Feature\Addons;

use Foo\Bar\TestAddonServiceProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Extend\Addon;
use Statamic\Facades;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('addons')]
class UpdateAddonSettingsTest extends TestCase
{
    use FakesRoles, PreventSavingStacheItemsToDisk;

    private $addon;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addon = $this->makeFromPackage(['slug' => 'test-addon']);

        Facades\Addon::shouldReceive('all')->andReturn(collect([$this->addon]));
        Facades\Addon::shouldReceive('get')->with('vendor/test-addon')->andReturn($this->addon);

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
    public function can_update_addon_settings()
    {
        $this->addon->settings()->values(['api_key' => 'original-api-key'])->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('addons.settings.edit', 'test-addon'), [
                'api_key' => 'new-api-key',
            ])
            ->assertOk()
            ->assertJson(['saved' => true]);

        $this->assertEquals('new-api-key', $this->addon->settings()->get('api_key'));
    }

    #[Test]
    public function can_update_addon_settings_with_configure_addons_permission()
    {
        $this->addon->settings()->values(['api_key' => 'original-api-key'])->save();

        $this->setTestRoles(['test' => ['access cp', 'configure addons']]);

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->patch(cp_route('addons.settings.edit', 'test-addon'), [
                'api_key' => 'new-api-key',
            ])
            ->assertOk()
            ->assertJson(['saved' => true]);

        $this->assertEquals('new-api-key', $this->addon->settings()->get('api_key'));
    }

    #[Test]
    public function can_update_addon_settings_with_edit_addon_settings_permission()
    {
        $this->addon->settings()->values(['api_key' => 'original-api-key'])->save();

        $this->setTestRoles(['test' => ['access cp', 'edit vendor/test-addon settings']]);

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->patch(cp_route('addons.settings.edit', 'test-addon'), [
                'api_key' => 'new-api-key',
            ])
            ->assertOk()
            ->assertJson(['saved' => true]);

        $this->assertEquals('new-api-key', $this->addon->settings()->get('api_key'));
    }

    #[Test]
    public function cant_update_addon_settings_without_permission()
    {
        $this->addon->settings()->values(['api_key' => 'original-api-key'])->save();

        $this->setTestRoles(['test' => ['access cp']]);

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->patch(cp_route('addons.settings.edit', 'test-addon'), [
                'api_key' => 'new-api-key',
            ])
            ->assertRedirect('/cp');

        $this->assertEquals('original-api-key', $this->addon->settings()->get('api_key'));
    }

    #[Test]
    public function cant_update_addon_settings_for_non_existent_addon()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('addons.settings.edit', 'non-existent-addon'), [
                'api_key' => 'new-api-key',
            ])
            ->assertNotFound();
    }

    #[Test]
    public function cant_update_addon_settings_when_addon_doesnt_have_any_settings()
    {
        $this->addon->settings()->values(['api_key' => 'original-api-key'])->save();

        // Forget the settings blueprint from the container.
        $this->app->offsetUnset('statamic.addons.test-addon.settings_blueprint');

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('addons.settings.edit', 'test-addon'), [
                'api_key' => 'new-api-key',
            ])
            ->assertNotFound();

        $this->assertEquals('original-api-key', $this->addon->settings()->get('api_key'));
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
