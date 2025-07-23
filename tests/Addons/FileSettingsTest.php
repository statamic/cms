<?php

namespace Addons;

use Foo\Bar\TestAddonServiceProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Addons\Addon;
use Statamic\Addons\FileSettings;
use Tests\TestCase;

#[Group('addons')]
class FileSettingsTest extends TestCase
{
    #[Test]
    public function it_gets_the_path()
    {
        $addon = $this->makeFromPackage();
        $settings = new FileSettings($addon);
        $this->assertEquals(resource_path('addons/test-addon.yaml'), $settings->path());
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
