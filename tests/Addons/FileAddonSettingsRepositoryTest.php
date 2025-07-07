<?php

namespace Tests\Addons;

use Foo\Bar\TestAddonServiceProvider;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Extend\Addon;
use Statamic\Extend\FileAddonSettings;
use Statamic\Extend\FileAddonSettingsRepository;
use Statamic\Facades;
use Tests\TestCase;

class FileAddonSettingsRepositoryTest extends TestCase
{
    private $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = new FileAddonSettingsRepository;
    }

    #[Test]
    public function it_makes_an_addon_settings_instance()
    {
        $addon = $this->makeFromPackage();

        $settings = $this->repository->make($addon, [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertInstanceOf(FileAddonSettings::class, $settings);
        $this->assertEquals($addon, $settings->addon());
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $settings->values()->all());
    }

    #[Test]
    public function it_gets_addon_settings()
    {
        $addon = $this->makeFromPackage();

        Facades\Addon::shouldReceive('all')->andReturn(collect([$addon]));
        Facades\Addon::shouldReceive('get')->with('vendor/test-addon')->andReturn($addon);

        File::ensureDirectoryExists(storage_path('statamic/addons/vendor/test-addon'));

        File::put(storage_path('statamic/addons/vendor/test-addon.yaml'), <<<'YAML'
foo: bar
baz: qux
YAML);

        $settings = $this->repository->find($addon->id());

        $this->assertInstanceOf(FileAddonSettings::class, $settings);
        $this->assertEquals($addon, $settings->addon());
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $settings->values()->all());
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

        $this->assertFileExists(storage_path('statamic/addons/vendor/test-addon.yaml'));

        $this->assertEquals(File::get(storage_path('statamic/addons/vendor/test-addon.yaml')), <<<'YAML'
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

        File::ensureDirectoryExists(storage_path('statamic/addons/vendor/test-addon'));

        File::put(storage_path('statamic/addons/vendor/test-addon.yaml'), '');

        $settings = $this->repository->find($addon->id());

        $settings->delete();

        $this->assertFileDoesNotExist(storage_path('statamic/addons/vendor/test-addon.yaml'));
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
