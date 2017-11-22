<?php

namespace Statamic\Tests\Extend;

use Tests\TestCase;

class FieldtypeTest extends TestCase
{
    private function inEachAddonLocation($callback)
    {
        $classes = [
            \Statamic\Addons\Test\TestFieldtype::class,             // Primary in root
            \Statamic\Addons\Test\SecondaryFieldtype::class,        // Secondary in root
            \Statamic\Addons\Test\Fieldtypes\TestFieldtype::class,      // Primary in namespace
            \Statamic\Addons\Test\Fieldtypes\SecondaryFieldtype::class, // Secondary in namespace
        ];

        foreach ($classes as $class) {
            tap(new $class, function ($addon) use ($callback) {
                $callback($addon);
            });
        }
    }

    /** @test */
    public function can_determine_if_primary_fieldtype()
    {
        // Located in root
        $this->assertTrue((new \Statamic\Addons\Test\TestFieldtype)->isPrimaryFieldtype());

        // Located in namespace
        $this->assertTrue((new \Statamic\Addons\Test\Fieldtypes\TestFieldtype)->isPrimaryFieldtype());
    }

    /** @test */
    public function can_determine_if_secondary_fieldtype()
    {
        // Located in root
        $this->assertFalse((new \Statamic\Addons\Test\SecondaryFieldtype)->isPrimaryFieldtype());

        // Located in namespace
        $this->assertFalse((new \Statamic\Addons\Test\Fieldtypes\SecondaryFieldtype)->isPrimaryFieldtype());
    }

    /** @test */
    public function gets_fieldtype_name()
    {
        $this->assertEquals('Test', (new \Statamic\Addons\Test\TestFieldtype)->getFieldtypeName());
        $this->assertEquals('Test', (new \Statamic\Addons\Test\Fieldtypes\TestFieldtype)->getFieldtypeName());
        $this->assertEquals('Test - Secondary', (new \Statamic\Addons\Test\SecondaryFieldtype)->getFieldtypeName());
        $this->assertEquals('Test - Secondary', (new \Statamic\Addons\Test\Fieldtypes\SecondaryFieldtype)->getFieldtypeName());
    }

    /** @test */
    public function gets_handle()
    {
        $this->assertEquals('test', (new \Statamic\Addons\Test\TestFieldtype)->getHandle());
        $this->assertEquals('test', (new \Statamic\Addons\Test\Fieldtypes\TestFieldtype)->getHandle());
        $this->assertEquals('test.secondary', (new \Statamic\Addons\Test\SecondaryFieldtype)->getHandle());
        $this->assertEquals('test.secondary', (new \Statamic\Addons\Test\Fieldtypes\SecondaryFieldtype)->getHandle());
    }

    /** @test */
    public function sets_and_gets_field_config()
    {
        $this->inEachAddonLocation(function ($ft) {
            $ft->setFieldConfig($config = [
                'name' => 'test_field',
                'max_items' => 123
            ]);

            $this->assertEquals($config, $ft->getFieldConfig());
            $this->assertEquals('test_field', $ft->getFieldConfig('name'));
            $this->assertEquals('fallback', $ft->getFieldConfig('unknown', 'fallback'));
            $this->assertNull($ft->getFieldConfig('unknown'));
        });
    }

    /** @test */
    public function gets_field_name()
    {
        $this->inEachAddonLocation(function ($ft) {
            $ft->setFieldConfig(['name' => 'test_field']);

            $this->assertEquals('test_field', $ft->getName());
        });
    }

    /** @test */
    public function gets_field_config_parameters()
    {
        $this->inEachAddonLocation(function ($addon) {
            $addon->setFieldConfig([
                'foo' => 'bar',
                'max_items' => '123',
                'truthy' => '1'
            ]);

            $this->assertEquals('bar', $addon->getParam('foo'));
            $this->assertEquals(123, $addon->getParamInt('max_items'));
            $this->assertTrue($addon->getParamBool('truthy'));
        });
    }
}
