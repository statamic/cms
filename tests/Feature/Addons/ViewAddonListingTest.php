<?php

namespace Tests\Feature\Addons;

use Foo\Bar\TestAddonServiceProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Addons\Addon;
use Statamic\Facades;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('addons')]
class ViewAddonListingTest extends TestCase
{
    use FakesRoles, PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_a_list_of_addons()
    {
        Facades\Addon::shouldReceive('all')->andReturn(collect([
            'statamic/seo-pro' => $seoPro = $this->makeFromPackage([
                'id' => 'statamic/seo-pro',
                'name' => 'SEO Pro',
                'slug' => 'seo-pro',
                'description' => 'An SEO addon for Statamic.',
                'developer' => 'Statamic',
                'version' => '6.7.0',
                'isCommercial' => true,
                'marketplaceId' => 10,
                'marketplaceSlug' => 'seo-pro',
                'marketplaceUrl' => 'https://statamic.com/addons/statamic/seo-pro',
            ]),
            'statamic/importer' => $importer = $this->makeFromPackage([
                'id' => 'statamic/importer',
                'name' => 'Importer',
                'slug' => 'importer',
                'description' => 'An importer addon for Statamic.',
                'developer' => 'Statamic',
                'version' => '1.8.4',
                'isCommercial' => false,
                'marketplaceId' => 20,
                'marketplaceSlug' => 'importer',
                'marketplaceUrl' => 'https://statamic.com/addons/statamic/importer',
            ]),
            'vendor/test-addon' => $testAddon = $this->makeFromPackage([
                'id' => 'vendor/test-addon',
                'name' => 'Test Addon',
                'slug' => 'test-addon',
                'description' => null,
                'developer' => 'Test Developer LLC',
                'version' => 'dev-main',
                'isCommercial' => false,
                'marketplaceId' => null,
                'marketplaceSlug' => null,
                'marketplaceUrl' => null,
            ]),
        ]));

        Facades\Addon::shouldReceive('get')->with('statamic/seo-pro')->andReturn($seoPro);
        Facades\Addon::shouldReceive('get')->with('statamic/importer')->andReturn($importer);
        Facades\Addon::shouldReceive('get')->with('vendor/test-addon')->andReturn($testAddon);

        // It doesn't need to be a real blueprint, it just needs to be bound.
        $this->app->bind('statamic.addons.seo-pro.settings_blueprint', fn () => []);

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('addons.index'))
            ->assertOk()
            ->assertViewHas('addons', [
                [
                    'name' => 'SEO Pro',
                    'version' => '6.7.0',
                    'developer' => 'Statamic',
                    'description' => 'An SEO addon for Statamic.',
                    'marketplace_url' => 'https://statamic.com/addons/statamic/seo-pro',
                    'updates_url' => cp_route('updater.product', 'seo-pro'),
                    'settings_url' => cp_route('addons.settings.edit', 'seo-pro'),
                ],
                [
                    'name' => 'Importer',
                    'version' => '1.8.4',
                    'developer' => 'Statamic',
                    'description' => 'An importer addon for Statamic.',
                    'marketplace_url' => 'https://statamic.com/addons/statamic/importer',
                    'updates_url' => cp_route('updater.product', 'importer'),
                    'settings_url' => null,
                ],
                [
                    'name' => 'Test Addon',
                    'version' => 'dev-main',
                    'developer' => 'Test Developer LLC',
                    'description' => null,
                    'marketplace_url' => null,
                    'updates_url' => null,
                    'settings_url' => null,
                ],
            ]);
    }

    #[Test]
    public function it_doesnt_show_a_list_of_addons_without_configure_addons_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->get(cp_route('addons.index'))
            ->assertRedirect('/cp');
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
