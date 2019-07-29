<?php

namespace Statamic\Testing\Extend;

use Tests\TestCase;
use Statamic\API\URL;
use Statamic\API\Path;
use Statamic\API\File;
use Statamic\Extend\Addon;

class AddonTest extends TestCase
{
    /** @test */
    function it_creates_an_instance_with_a_name()
    {
        $this->assertInstanceOf(Addon::class, Addon::create('TestAddon'));
    }

    /** @test */
    public function it_gets_the_id()
    {
        $this->assertEquals(
            'TestAddon',
            Addon::create('TestAddon')->id()
        );
    }

    /** @test */
    public function it_gets_the_handle()
    {
        $this->assertEquals(
            'test_addon',
            Addon::create('TestAddon')->handle()
        );
    }

    /** @test */
    public function it_gets_the_slug()
    {
        $this->assertEquals(
            'test-addon',
            Addon::create('test addon')->slug()
        );
    }

    /** @test */
    function it_creates_an_instance_from_a_package()
    {
        $addon = $this->createFromPackage([]);

        $this->assertInstanceOf(Addon::class, $addon);
        $this->assertEquals('TestAddon', $addon->id());
        $this->assertEquals('Test Addon', $addon->name());
        $this->assertEquals('Test description', $addon->description());
        $this->assertEquals('test-vendor/test-addon', $addon->package());
        $this->assertEquals('Vendor\\TestAddon', $addon->namespace());
        $this->assertEquals('/path/to/addon', $addon->directory());
        $this->assertEquals('http://test-url.com', $addon->url());
        $this->assertEquals('Test Developer LLC', $addon->developer());
        $this->assertEquals('http://test-developer.com', $addon->developerUrl());
        $this->assertEquals('1.0', $addon->version());
    }

    /** @test */
    public function it_checks_if_a_file_exists()
    {
        $addon = Addon::create('Test Addon')->directory('/path/to/addon');

        File::shouldReceive('exists')->with('/path/to/addon/test.txt')->andReturnTrue();
        File::shouldReceive('exists')->with('/path/to/addon/notfound.txt')->andReturnFalse();

        $this->assertTrue($addon->hasFile('test.txt'));
        $this->assertFalse($addon->hasFile('notfound.txt'));
    }

    /** @test */
    public function it_gets_file_contents()
    {
        $addon = Addon::create('Test Addon')->directory('/path/to/addon');

        File::shouldReceive('get')->with('/path/to/addon/test.txt')->andReturn('the file contents');

        $this->assertEquals('the file contents', $addon->getFile('test.txt'));
    }

    /** @test */
    public function it_writes_file_contents()
    {
        $addon = Addon::create('Test Addon')->directory('/path/to/addon');

        File::shouldReceive('put')->with('/path/to/addon/test.txt', 'the file contents');

        $addon->putFile('test.txt', 'the file contents');
    }

    /** @test */
    function it_doesnt_allow_getting_files_if_no_directory_is_set()
    {
        File::spy();
        $addon = $this->createFromPackage(['directory' => null]);

        try {
            $addon->getFile('foo.txt', 'foo');
        } catch (\Exception $e) {
            $this->assertEquals('Cannot get files without a directory specified.', $e->getMessage());
            File::shouldNotHaveReceived('get');
            return;
        }

        $this->fail('Exception was not thrown.');
    }

    /** @test */
    function it_doesnt_allow_checking_for_files_if_no_directory_is_set()
    {
        File::spy();
        $addon = $this->createFromPackage(['directory' => null]);

        try {
            $addon->hasFile('foo.txt', 'foo');
        } catch (\Exception $e) {
            $this->assertEquals('Cannot check files without a directory specified.', $e->getMessage());
            File::shouldNotHaveReceived('get');
            return;
        }

        $this->fail('Exception was not thrown.');
    }



    /** @test */
    function it_doesnt_allow_writing_files_if_no_directory_is_set()
    {
        File::spy();
        $addon = $this->createFromPackage(['directory' => null]);

        try {
            $addon->putFile('foo.txt', 'foo');
        } catch (\Exception $e) {
            $this->assertEquals('Cannot write files without a directory specified.', $e->getMessage());
            File::shouldNotHaveReceived('put');
            return;
        }

        $this->fail('Exception was not thrown.');
    }

    /** @test */
    public function it_gets_the_name_from_id_if_it_wasnt_specified()
    {
        $addon = $this->createFromPackage([
            'name' => null,
            'id' => 'BarBaz',
        ]);

        $this->assertEquals('BarBaz', $addon->name());
    }

    /** @test */
    public function it_checks_if_commercial()
    {
        $this->assertTrue($this->createFromPackage(['isCommercial' => true])->isCommercial());
        $this->assertFalse($this->createFromPackage(['isCommercial' => false])->isCommercial());
        $this->assertFalse($this->createFromPackage([])->isCommercial());
    }

    /** @test */
    public function it_gets_the_license_key()
    {
        config(['test_addon' => ['license_key' => 'TESTLICENSEKEY']]);

        $this->assertEquals('TESTLICENSEKEY', Addon::create('TestAddon')->licenseKey());
    }

    public function it_gets_the_autoloaded_directory()
    {
        $addon = $this->createFromPackage(['autoload' => 'src']);

        $this->assertEquals('src', $addon->autoload());
    }

    private function createFromPackage($attributes)
    {
        return Addon::createFromPackage(array_merge([
            'id' => 'TestAddon',
            'package' => 'test-vendor/test-addon',
            'name' => 'Test Addon',
            'description' => 'Test description',
            'namespace' => 'Vendor\\TestAddon',
            'directory' => '/path/to/addon',
            'autoload' => 'src',
            'url' => 'http://test-url.com',
            'developer' => 'Test Developer LLC',
            'developerUrl' => 'http://test-developer.com',
            'version' => '1.0',
        ], $attributes));
    }
}
