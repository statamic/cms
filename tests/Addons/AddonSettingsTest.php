<?php

namespace Tests\Addons;

use Foo\Bar\TestAddonServiceProvider;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Extend\AddonSettingsRepository;
use Statamic\Events\AddonSettingsSaved;
use Statamic\Extend\Addon;
use Statamic\Extend\AddonSettings as AddonSettings;
use Tests\TestCase;

class AddonSettingsTest extends TestCase
{
    #[Test]
    public function it_returns_the_addon()
    {
        $addon = $this->makeFromPackage();
        $settings = new AddonSettings($addon, ['foo' => 'bar']);

        $this->assertEquals($addon, $settings->addon());
    }

    #[Test]
    public function it_returns_the_values()
    {
        $addon = $this->makeFromPackage();
        $settings = new AddonSettings($addon, ['website_name' => '{{ config:app:url }}', 'foo' => 'bar']);

        $this->assertIsArray($settings->values());
        $this->assertEquals([
            'website_name' => 'http://localhost',
            'foo' => 'bar',
        ], $settings->values());
    }

    #[Test]
    public function it_returns_the_raw_values()
    {
        $addon = $this->makeFromPackage();
        $settings = new AddonSettings($addon, ['website_name' => '{{ config:app:url }}', 'foo' => 'bar']);

        $this->assertIsArray($settings->rawValues());
        $this->assertEquals([
            'website_name' => '{{ config:app:url }}',
            'foo' => 'bar',
        ], $settings->rawValues());
    }

    #[Test]
    public function it_gets_a_value()
    {
        $addon = $this->makeFromPackage();
        $settings = new AddonSettings($addon, ['foo' => 'bar']);

        $this->assertEquals('bar', $settings->get('foo'));
        $this->assertNull($settings->get('nonexistent'));
        $this->assertEquals('default', $settings->get('nonexistent', 'default'));
    }

    #[Test]
    public function it_checks_if_a_value_exists()
    {
        $addon = $this->makeFromPackage();
        $settings = new AddonSettings($addon, ['foo' => 'bar']);

        $this->assertTrue($settings->has('foo'));
        $this->assertFalse($settings->has('nonexistent'));
    }

    #[Test]
    public function it_sets_a_value()
    {
        $addon = $this->makeFromPackage();
        $settings = new AddonSettings($addon, ['foo' => 'bar']);

        $settings->set('baz', 'qux');

        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $settings->values());
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $settings->rawValues());
    }

    #[Test]
    public function it_merges_settings()
    {
        $addon = $this->makeFromPackage();
        $settings = new AddonSettings($addon, ['foo' => 'bar']);

        $settings->merge(['baz' => 'qux']);

        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $settings->values());
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $settings->rawValues());
    }

    #[Test]
    public function it_saves_settings()
    {
        Event::fake();

        $addon = $this->makeFromPackage();
        $settings = new AddonSettings($addon, ['website_name' => '{{ config:app:url }}', 'foo' => 'bar']);

        $this->mock(AddonSettingsRepository::class, function ($mock) use ($settings) {
            $mock->shouldReceive('save')->with($settings)->andReturn(true)->once();
        });

        $settings->save();

        Event::assertDispatched(AddonSettingsSaved::class);
    }

    #[Test]
    public function it_deletes_settings()
    {
        $addon = $this->makeFromPackage();
        $settings = new AddonSettings($addon, ['website_name' => '{{ config:app:url }}', 'foo' => 'bar']);

        $this->mock(AddonSettingsRepository::class, function ($mock) use ($settings) {
            $mock->shouldReceive('delete')->with($settings)->andReturn(true)->once();
        });

        $settings->delete();
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
