<?php

namespace Tests\Extend;

use Tests\TestCase;
use Statamic\API\Path;
use Statamic\API\Site;
use Statamic\API\Config;
use Statamic\Extend\Addon;
use Statamic\Config\Addons;
use Tests\ModifiesAddonManifest;
use Foo\Bar\Example as Extension;
use Illuminate\Support\Facades\Event;
use Statamic\Extend\Management\Manifest;
use Statamic\Extend\Contextual\ContextualJs;
use Statamic\Extend\Contextual\ContextualCss;
use Statamic\Extend\Contextual\ContextualImage;
use Statamic\Extend\Contextual\ContextualBlink;
use Statamic\Extend\Contextual\ContextualFlash;
use Statamic\Extend\Contextual\ContextualCache;
use Statamic\Extend\Contextual\ContextualCookie;
use Statamic\Extend\Contextual\ContextualSession;
use Statamic\Extend\Contextual\ContextualStorage;
use Statamic\Extend\Contextual\ContextualResource;

class ExtensibleTest extends TestCase
{
    use ModifiesAddonManifest;

    public function setUp()
    {
        parent::setUp();

        $this->fakeManifest();
    }

    /** @test */
    function gets_addon_class_object()
    {
        $example = new Extension;

        $addon = $example->getAddon();
        $this->assertInstanceOf(\Statamic\Extend\Addon::class, $addon);
        $this->assertEquals('Foo\Bar', $addon->namespace());
    }

    /** @test */
    public function gets_addon_class_name()
    {
        // Should be the "Bison" in "Statamic\Addons\Bison\SomeTags"
        $this->assertEquals('Bar', (new Extension)->getAddonClassName());
        $this->assertEquals('Bar', (new Extension)->addon_name);
    }

    /** @test */
    function gets_addon_name()
    {
        $this->assertEquals('The Bar', (new Extension)->getAddonName());

        $this->overrideManifest(['name' => null]);

        $this->assertEquals('Bar', (new Extension)->getAddonName());
    }

    // /** @test */
    // public function gets_class_name_without_suffix()
    // {
    //     // Should be the "Foo" in "Statamic\Addons\Bison\FooModifier"
    //     // probably irrelevant now because of container binding only used to get secondary fieldtypes.

    //     // $this->assertEquals('Test', (new \Statamic\Addons\Test\TestFieldtype)->getClassNameWithoutSuffix());
    //     // $this->assertEquals('Test', (new \Statamic\Addons\Test\Fieldtypes\TestFieldtype)->getClassNameWithoutSuffix());
    //     // $this->assertEquals('Secondary', (new \Statamic\Addons\Test\SecondaryFieldtype)->getClassNameWithoutSuffix());
    //     // $this->assertEquals('Secondary', (new \Statamic\Addons\Test\Fieldtypes\SecondaryFieldtype)->getClassNameWithoutSuffix());
    // }

    /** @test */
    public function gets_directory()
    {
        // Should be the full path to the addon root
        $this->overrideManifest(['directory' => '/path/to/directory']);

        $this->assertEquals('/path/to/directory', (new Extension)->getDirectory());
    }

    /** @test */
    public function creates_email_builder_and_sets_location_to_views_directory()
    {
        $this->overrideManifest(['directory' => '/path/to/directory']);

        $this->assertEquals(
            '/path/to/directory/resources/views',
            (new Extension)->email()->message()->templatePath()
        );
    }

    /** @test */
    public function emits_namespaced_events()
    {
        // Event emitted should be prefixed with the addon class name and a period.
        Event::fake();

        (new Extension)->emitEvent('testevent', 'something');

        Event::assertDispatched('Bar.testevent', function ($e, $payload) {
            return $payload = 'something';
        });
    }

    /** @test */
    public function gets_config_vars()
    {
        // Should do the typical config thing with fallbacks
        $config = [
            'foo' => 'bar',
            'baz' => 'qux'
        ];

        config(['bar' => $config]);

        $addon = new Extension;
        $this->assertEquals($config, $addon->getConfig());
        $this->assertEquals('bar', $addon->getConfig('foo'));
        $this->assertEquals('bar', $addon->getConfig(['unknown', 'foo']));
        $this->assertEquals('fallback', $addon->getConfig('unknown', 'fallback'));
        $this->assertNull($addon->getConfig('unknown'));
    }

    /** @test */
    public function gets_config_vars_as_booleans()
    {
        // As above but bools

        $config = [
            'truthy' => true,
            'falsey' => false
        ];

        config(['bar' => $config]);

        $addon = new Extension;
        $this->assertTrue($addon->getConfigBool('truthy'));
        $this->assertTrue($addon->getConfigBool(['unknown', 'truthy']));
        $this->assertFalse($addon->getConfigBool(['unknown', 'falsey']));
        $this->assertTrue($addon->getConfigBool('unknown', true));
        $this->assertFalse($addon->getConfigBool('unknown', false));
        $this->assertFalse($addon->getConfigBool('unknown'));
    }

    /** @test */
    public function gets_config_vars_as_integers()
    {
        // As above but ints

        $config = [
            'one' => '1',
            'two' => '2'
        ];

        config(['bar' => $config]);

        $addon = new Extension;
        $this->assertEquals(1, $addon->getConfigInt('one'));
        $this->assertEquals(1, $addon->getConfigInt(['unknown', 'one']));
        $this->assertEquals(3, $addon->getConfigInt('unknown', '3'));
        $this->assertEquals(0, $addon->getConfigInt('unknown'));
    }

    /** @test */
    public function builds_event_urls_and_aliases_to_action_urls()
    {
        // How these urls work needs some grapes.

        Site::setConfig('sites.en.url', 'http://example.com');

        $addon = new Extension;
        $this->assertEquals('/!/bar/foo', $addon->eventUrl('foo'));
        $this->assertEquals('/!/bar/foo', $addon->actionUrl('foo'));
        $this->assertEquals('http://example.com/!/bar/foo', $addon->eventUrl('foo', false));
        $this->assertEquals('http://example.com/!/bar/foo', $addon->actionUrl('foo', false));
    }

    /** @test */
    public function gets_contextual_classes()
    {
        // Should be initialized correctly.

        $addon = new Extension;

        $this->assertInstanceOf(ContextualBlink::class, $addon->blink);
        $this->assertInstanceOf(ContextualCache::class, $addon->cache);
        $this->assertInstanceOf(ContextualSession::class, $addon->session);
        $this->assertInstanceOf(ContextualFlash::class, $addon->flash);
        $this->assertInstanceOf(ContextualStorage::class, $addon->storage);
        $this->assertInstanceOf(ContextualCookie::class, $addon->cookie);
        $this->assertInstanceOf(ContextualResource::class, $addon->resource);
        $this->assertInstanceOf(ContextualCss::class, $addon->css);
        $this->assertInstanceOf(ContextualJs::class, $addon->js);
        $this->assertInstanceOf(ContextualImage::class, $addon->img);
    }
}
