<?php

namespace Tests\Addons;

use Foo\Bar\TestAddonServiceProvider;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Addons\Addon;
use Statamic\Addons\Settings as AbstractSettings;
use Statamic\Contracts\Addons\SettingsRepository;
use Statamic\Events\AddonSettingsSaved;
use Statamic\Events\AddonSettingsSaving;
use Tests\TestCase;

#[Group('addons')]
class SettingsTest extends TestCase
{
    #[Test]
    public function it_returns_the_addon()
    {
        $addon = $this->makeFromPackage();
        $settings = new Settings($addon, ['foo' => 'bar']);

        $this->assertEquals($addon, $settings->addon());
    }

    #[Test]
    public function it_returns_the_values()
    {
        $addon = $this->makeFromPackage();
        $settings = new Settings($addon, [
            'website_name' => '{{ config:app:url }}',
            'foo' => 'bar',
            'baz' => [
                'qux' => '{{ config:app:name }}',
            ],
        ]);

        $this->assertIsArray($settings->all());
        $this->assertSame([
            'website_name' => 'http://localhost',
            'foo' => 'bar',
            'baz' => [
                'qux' => 'Laravel',
            ],
        ], $settings->all());
    }

    #[Test]
    public function it_returns_the_raw_values()
    {
        $addon = $this->makeFromPackage();
        $settings = new Settings($addon, [
            'website_name' => '{{ config:app:url }}',
            'foo' => 'bar',
            'baz' => [
                'qux' => '{{ config:app:name }}',
            ],
        ]);

        $this->assertIsArray($settings->raw());
        $this->assertSame([
            'website_name' => '{{ config:app:url }}',
            'foo' => 'bar',
            'baz' => [
                'qux' => '{{ config:app:name }}',
            ],
        ], $settings->raw());
    }

    #[Test]
    public function it_gets_a_value()
    {
        $addon = $this->makeFromPackage();
        $settings = new Settings($addon, ['foo' => 'bar']);

        $this->assertEquals('bar', $settings->get('foo'));
        $this->assertNull($settings->get('nonexistent'));
        $this->assertEquals('default', $settings->get('nonexistent', 'default'));
    }

    #[Test]
    public function it_gets_a_dotted_value()
    {
        $addon = $this->makeFromPackage();
        $settings = new Settings($addon, ['foo' => ['bar' => 'baz']]);

        $this->assertEquals('baz', $settings->get('foo.bar'));
        $this->assertEquals('default', $settings->get('nonexistent', 'default'));
    }

    #[Test]
    public function it_sets_a_value()
    {
        config([
            'test' => ['a' => 'A', 'b' => 'B'],
            'statamic.system.view_config_allowlist' => ['@default', 'test.a', 'test.b'],
        ]);
        $addon = $this->makeFromPackage();
        $settings = new Settings($addon, ['foo' => 'bar']);

        $settings->set('baz', '{{ config:app:name }}');

        $this->assertEquals(['foo' => 'bar', 'baz' => 'Laravel'], $settings->all());
        $this->assertEquals(['foo' => 'bar', 'baz' => '{{ config:app:name }}'], $settings->raw());

        $settings->set('qux', $raw = [
            'alfa' => 'bravo',
            'charlie' => '{{ config:test:a }}',
            'delta' => [
                'echo' => '{{ config:test:b }}',
            ],
        ]);
        $this->assertEquals([
            'foo' => 'bar',
            'baz' => 'Laravel',
            'qux' => [
                'alfa' => 'bravo',
                'charlie' => 'A',
                'delta' => ['echo' => 'B'],
            ],
        ], $settings->all());
        $this->assertEquals([
            'foo' => 'bar',
            'baz' => '{{ config:app:name }}',
            'qux' => $raw,
        ], $settings->raw());
    }

    #[Test]
    public function it_sets_a_dotted_value()
    {
        $addon = $this->makeFromPackage();
        $settings = new Settings($addon, []);

        $settings->set('foo.baz', 'dotted');

        $this->assertEquals(['foo' => ['baz' => 'dotted']], $settings->all());
    }

    #[Test]
    public function it_sets_all_values()
    {
        config([
            'test' => ['a' => 'A', 'b' => 'B'],
            'statamic.system.view_config_allowlist' => ['@default', 'test.a', 'test.b'],
        ]);

        $this->app->bind('statamic.addons.test-addon.settings_blueprint', fn () => [
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'api_key', 'field' => ['type' => 'text', 'default' => 'my-default-key']],
                                ['handle' => 'another_field', 'field' => ['type' => 'text', 'default' => 'default-value']],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $addon = $this->makeFromPackage();
        $settings = new Settings($addon, ['foo' => 'bar']);

        $settings->set($raw = [
            'alfa' => 'bravo',
            'charlie' => '{{ config:test:a }}',
            'delta' => [
                'echo' => '{{ config:test:b }}',
            ],
        ]);

        $this->assertEquals([
            'api_key' => 'my-default-key',
            'another_field' => 'default-value',
            'alfa' => 'bravo',
            'charlie' => 'A',
            'delta' => ['echo' => 'B'],
        ], $settings->all());
        $this->assertEquals($raw, $settings->raw());
    }

    #[Test]
    public function it_applies_blueprint_defaults_to_resolved_settings()
    {
        $this->app->bind('statamic.addons.test-addon.settings_blueprint', fn () => [
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'api_key', 'field' => ['type' => 'text', 'default' => 'my-default-key']],
                                ['handle' => 'no_default_field', 'field' => ['type' => 'text']],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $addon = $this->makeFromPackage();
        $settings = new Settings($addon, []);

        $this->assertEquals('my-default-key', $settings->get('api_key'));
        $this->assertNull($settings->get('no_default_field'));
    }

    #[Test]
    public function it_does_not_include_blueprint_defaults_in_raw_settings()
    {
        $this->app->bind('statamic.addons.test-addon.settings_blueprint', fn () => [
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'api_key', 'field' => ['type' => 'text', 'default' => 'my-default-key']],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $addon = $this->makeFromPackage();
        $settings = new Settings($addon, []);

        $this->assertArrayNotHasKey('api_key', $settings->raw());
        $this->assertSame([], $settings->raw());
    }

    #[Test]
    public function saved_settings_take_precedence_over_blueprint_defaults()
    {
        $this->app->bind('statamic.addons.test-addon.settings_blueprint', fn () => [
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'api_key', 'field' => ['type' => 'text', 'default' => 'my-default-key']],
                                ['handle' => 'another_field', 'field' => ['type' => 'text', 'default' => 'default-value']],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $addon = $this->makeFromPackage();
        $settings = new Settings($addon, ['api_key' => 'saved-key']);

        $this->assertEquals('saved-key', $settings->get('api_key'));
        $this->assertEquals('default-value', $settings->get('another_field'));
        $this->assertSame(['api_key' => 'saved-key'], $settings->raw());
    }

    #[Test]
    public function it_saves_settings()
    {
        Event::fake();

        $addon = $this->makeFromPackage();
        $settings = new Settings($addon, ['website_name' => '{{ config:app:url }}', 'foo' => 'bar']);

        $this->mock(SettingsRepository::class, function ($mock) use ($settings) {
            $mock->shouldReceive('save')->with($settings)->andReturn(true)->once();
        });

        $return = $settings->save();

        $this->assertTrue($return);

        Event::assertDispatched(AddonSettingsSaving::class);
        Event::assertDispatched(AddonSettingsSaved::class);
    }

    #[Test]
    public function if_saving_event_returns_false_the_settings_dont_save()
    {
        Event::fake([AddonSettingsSaved::class]);

        Event::listen(AddonSettingsSaving::class, function () {
            return false;
        });

        $addon = $this->makeFromPackage();
        $settings = new Settings($addon, ['website_name' => '{{ config:app:url }}', 'foo' => 'bar']);

        $this->mock(SettingsRepository::class, function ($mock) use ($settings) {
            $mock->shouldReceive('save')->with($settings)->andReturn(true)->never();
        });

        $return = $settings->save();

        $this->assertFalse($return);

        Event::assertNotDispatched(AddonSettingsSaved::class);
    }

    #[Test]
    public function it_deletes_settings()
    {
        $addon = $this->makeFromPackage();
        $settings = new Settings($addon, ['website_name' => '{{ config:app:url }}', 'foo' => 'bar']);

        $this->mock(SettingsRepository::class, function ($mock) use ($settings) {
            $mock->shouldReceive('delete')->with($settings)->andReturn(true)->once();
        });

        $return = $settings->delete();

        $this->assertTrue($return);
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

class Settings extends AbstractSettings
{
}
