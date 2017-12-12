<?php

namespace Tests\Extend;

use Tests\TestCase;
use Tests\ModifiesAddonManifest;

class FieldtypeTest extends TestCase
{
    use ModifiesAddonManifest;

    public function setUp()
    {
        parent::setUp();

        $this->fakeManifest();
    }

    /** @test */
    public function gets_name_from_class()
    {
        $this->assertEquals('Bar', (new \Foo\Bar\Fieldtypes\Bar)->getFieldtypeName());
    }

    /** @test */
    function gets_name_from_class_without_fieldtype_suffix()
    {
        $this->assertEquals('Bar', (new \Foo\Bar\Fieldtypes\BarFieldtype)->getFieldtypeName());
    }

    /** @test */
    function appends_addon_name_if_class_is_different()
    {
        $this->assertEquals('Text - Bar', (new \Foo\Bar\Fieldtypes\Text)->getFieldtypeName());
    }

    /** @test */
    public function gets_handle()
    {
        $this->app['statamic.fieldtypes']['bar'] = \Foo\Bar\Fieldtypes\Bar::class;

        $this->assertEquals('bar', (new \Foo\Bar\Fieldtypes\Bar)->getHandle());
    }

    /** @test */
    public function sets_and_gets_field_config()
    {
        $ft = new \Foo\Bar\Fieldtypes\Text;

        $ft->setFieldConfig($config = [
            'name' => 'test_field',
            'max_items' => 123
        ]);

        $this->assertEquals($config, $ft->getFieldConfig());
        $this->assertEquals('test_field', $ft->getFieldConfig('name'));
        $this->assertEquals('fallback', $ft->getFieldConfig('unknown', 'fallback'));
        $this->assertNull($ft->getFieldConfig('unknown'));
    }

    /** @test */
    public function gets_field_name()
    {
        $ft = new \Foo\Bar\Fieldtypes\Text;

        $ft->setFieldConfig(['name' => 'test_field']);

        $this->assertEquals('test_field', $ft->getName());
    }

    /** @test */
    public function gets_field_config_parameters()
    {
        $addon = new \Foo\Bar\Fieldtypes\Text;

        $addon->setFieldConfig([
            'foo' => 'bar',
            'max_items' => '123',
            'truthy' => '1'
        ]);

        $this->assertEquals('bar', $addon->getParam('foo'));
        $this->assertEquals(123, $addon->getParamInt('max_items'));
        $this->assertTrue($addon->getParamBool('truthy'));
    }
}
