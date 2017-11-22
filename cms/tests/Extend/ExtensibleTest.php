<?php

namespace Tests\Extend;

use Tests\TestCase;
use Statamic\API\Path;
use Statamic\API\Config;
use Statamic\Config\Addons;
use Statamic\Extend\Contextual\ContextualJs;
use Statamic\Extend\Contextual\ContextualCss;
use Statamic\Extend\Contextual\ContextualBlink;
use Statamic\Extend\Contextual\ContextualCache;
use Statamic\Extend\Contextual\ContextualFlash;
use Statamic\Extend\Contextual\ContextualImage;
use Statamic\Extend\Contextual\ContextualCookie;
use Statamic\Extend\Contextual\ContextualSession;
use Statamic\Extend\Contextual\ContextualStorage;
use Statamic\Extend\Contextual\ContextualResource;

class ExtensibleTest extends TestCase
{
    private function inEachAddonLocation($callback)
    {
        $classes = [
            \Statamic\Addons\Test\TestThing::class,             // Primary in root
            \Statamic\Addons\Test\SecondaryThing::class,        // Secondary in root
            \Statamic\Addons\Test\Things\TestThing::class,      // Primary in namespace
            \Statamic\Addons\Test\Things\SecondaryThing::class, // Secondary in namespace
        ];

        foreach ($classes as $class) {
            tap(new $class, function ($addon) use ($callback) {
                $callback($addon);
            });
        }
    }

    /** @test */
    public function gets_addon_class_name()
    {
        $this->inEachAddonLocation(function ($addon) {
            $this->assertEquals('Test', $addon->getAddonClassName());
            $this->assertEquals('Test', $addon->addon_name);
        });
    }

    /** @test */
    public function gets_class_name_without_suffix()
    {
        $this->assertEquals('Test', (new \Statamic\Addons\Test\TestFieldtype)->getClassNameWithoutSuffix());
        $this->assertEquals('Test', (new \Statamic\Addons\Test\Fieldtypes\TestFieldtype)->getClassNameWithoutSuffix());
        $this->assertEquals('Secondary', (new \Statamic\Addons\Test\SecondaryFieldtype)->getClassNameWithoutSuffix());
        $this->assertEquals('Secondary', (new \Statamic\Addons\Test\Fieldtypes\SecondaryFieldtype)->getClassNameWithoutSuffix());
    }

    /** @test */
    public function gets_addon_type()
    {
        $this->inEachAddonLocation(function ($addon) {
            $this->assertEquals('Thing', $addon->getAddonType());
            $this->assertEquals('Thing', $addon->addon_type);
        });
    }

    /** @test */
    public function addon_name_is_accessible_by_property_access_for_backwards_compatibility()
    {
        $this->inEachAddonLocation(function ($addon) {
            $this->assertEquals('Test', $addon->addon_name);
        });
    }

    /** @test */
    public function gets_directory()
    {
        $this->inEachAddonLocation(function ($addon) {
            $this->assertEquals(
                'site/addons/Test',
                Path::makeRelative($addon->getDirectory())
            );
        });
    }

    /** @test */
    public function determines_if_third_party()
    {
        $this->inEachAddonLocation(function ($addon) {
            $this->assertTrue($addon->isThirdParty());
            $this->assertFalse($addon->isFirstParty());
        });
    }

    /** @test */
    public function creates_email_builder_and_sets_location_to_views_directory()
    {
        $this->inEachAddonLocation(function ($addon) {
            $builder = $addon->email();

            $this->assertEquals(
                'site/addons/Test/resources/views',
                Path::makeRelative($builder->message()->templatePath())
            );
        });
    }

    /** @test */
    public function emits_namespaced_events()
    {
        $this->inEachAddonLocation(function ($addon) {
            $addon->emitEvent('foo', 'bar');

            \Event::assertFired('Test.foo', function ($e, $payload) {
                return $payload === 'bar';
            });
        });
    }

    /** @test */
    public function gets_config_vars()
    {
        $config = [
            'foo' => 'bar',
            'baz' => 'qux'
        ];

        app(Addons::class)->hydrate(['test' => $config]);

        $this->inEachAddonLocation(function ($addon) use ($config) {
            $this->assertEquals($config, $addon->getConfig());
            $this->assertEquals('bar', $addon->getConfig('foo'));
            $this->assertEquals('bar', $addon->getConfig(['unknown', 'foo']));
            $this->assertEquals('fallback', $addon->getConfig('unknown', 'fallback'));
            $this->assertNull($addon->getConfig('unknown'));
        });
    }

    /** @test */
    public function gets_config_vars_as_booleans()
    {
        $config = [
            'truthy' => true,
            'falsey' => false
        ];

        app(Addons::class)->hydrate(['test' => $config]);

        $this->inEachAddonLocation(function ($addon) use ($config) {
            $this->assertTrue($addon->getConfigBool('truthy'));
            $this->assertTrue($addon->getConfigBool(['unknown', 'truthy']));
            $this->assertFalse($addon->getConfigBool(['unknown', 'falsey']));
            $this->assertTrue($addon->getConfigBool('unknown', true));
            $this->assertFalse($addon->getConfigBool('unknown', false));
            $this->assertFalse($addon->getConfigBool('unknown'));
        });
    }

    /** @test */
    public function gets_config_vars_as_integers()
    {
        $config = [
            'one' => '1',
            'two' => '2'
        ];

        app(Addons::class)->hydrate(['test' => $config]);

        $this->inEachAddonLocation(function ($addon) use ($config) {
            $this->assertEquals(1, $addon->getConfigInt('one'));
            $this->assertEquals(1, $addon->getConfigInt(['unknown', 'one']));
            $this->assertEquals(3, $addon->getConfigInt('unknown', '3'));
            $this->assertEquals(0, $addon->getConfigInt('unknown'));
        });
    }

    /** @test */
    public function builds_event_urls_and_aliases_to_action_urls()
    {
        Config::set('system.locales.en.url', 'http://example.com');

        $this->inEachAddonLocation(function ($addon) {
            $this->assertEquals('/!/Test/foo', $addon->eventUrl('foo'));
            $this->assertEquals('/!/Test/foo', $addon->actionUrl('foo'));
            $this->assertEquals('http://example.com/!/Test/foo', $addon->eventUrl('foo', false));
            $this->assertEquals('http://example.com/!/Test/foo', $addon->actionUrl('foo', false));
        });
    }

    /** @test */
    public function gets_contextual_classes()
    {
        $this->inEachAddonLocation(function ($addon) {
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
        });
    }
}
