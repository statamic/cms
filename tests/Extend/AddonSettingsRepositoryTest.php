<?php

namespace Extend;

use Foo\Bar\TestAddonServiceProvider;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Extend\Addon;
use Statamic\Extend\AddonSettings;
use Statamic\Extend\AddonSettingsRepository;
use Statamic\Facades;
use Tests\TestCase;

#[Group('addons')]
class AddonSettingsRepositoryTest extends TestCase
{
    private $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = new AddonSettingsRepository;

        $this->app['files']->ensureDirectoryExists(resource_path('addons'));
    }

    #[Test]
    public function it_makes_an_addon_settings_instance()
    {
        $addon = $this->makeFromPackage();

        $settings = $this->repository->make($addon, [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertInstanceOf(AddonSettings::class, $settings);
        $this->assertEquals($addon, $settings->addon());
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $settings->values());
    }

    #[Test]
    public function it_gets_addon_settings()
    {
        $addon = $this->makeFromPackage();

        Facades\Addon::shouldReceive('all')->andReturn(collect([$addon]));
        Facades\Addon::shouldReceive('get')->with('vendor/test-addon')->andReturn($addon);

        File::put(resource_path('addons/test-addon.yaml'), <<<'YAML'
foo: bar
baz: qux
YAML);

        $settings = $this->repository->find($addon->id());

        $this->assertInstanceOf(AddonSettings::class, $settings);
        $this->assertEquals($addon, $settings->addon());
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $settings->values());
    }

    #[Test]
    public function it_saves_addon_settings()
    {
        $addon = $this->makeFromPackage();

        $settings = $this->repository->make($addon, [
            'foo' => 'bar',
            'baz' => 'qux',
            'quux' => null, // Should be filtered out.
        ]);

        $settings->save();

        $this->assertFileExists(resource_path('addons/test-addon.yaml'));

        $this->assertEquals(File::get(resource_path('addons/test-addon.yaml')), <<<'YAML'
foo: bar
baz: qux

YAML);
    }

    #[Test]
    public function it_deletes_addon_settings()
    {
        $addon = $this->makeFromPackage();

        Facades\Addon::shouldReceive('all')->andReturn(collect([$addon]));
        Facades\Addon::shouldReceive('get')->with('vendor/test-addon')->andReturn($addon);

        File::put(resource_path('addons/test-addon.yaml'), '');

        $settings = $this->repository->find($addon->id());

        $settings->delete();

        $this->assertFileDoesNotExist(resource_path('addons/test-addon.yaml'));
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
