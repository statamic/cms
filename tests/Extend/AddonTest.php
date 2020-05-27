<?php

namespace Statamic\Testing\Extend;

use Statamic\Extend\Addon;
use Statamic\Facades\File;
use Tests\TestCase;

class AddonTest extends TestCase
{
    /** @test */
    public function it_creates_an_instance_with_a_name()
    {
        $this->assertInstanceOf(Addon::class, Addon::make('TestAddon'));
    }

    /** @test */
    public function it_gets_the_id()
    {
        $this->assertEquals(
            'vendor/foo-bar',
            Addon::make('vendor/foo-bar')->id()
        );
    }

    /** @test */
    public function it_gets_the_handle()
    {
        $this->assertEquals(
            'foo_bar',
            Addon::make('vendor/foo-bar')->handle()
        );
    }

    /** @test */
    public function it_gets_the_slug()
    {
        $this->assertEquals(
            'foo-bar',
            Addon::make('vendor/foo-bar')->slug()
        );
    }

    /** @test */
    public function it_gets_the_vendor_name()
    {
        $this->assertEquals(
            'vendor-name',
            Addon::make('vendor-name/package-name')->vendorName()
        );
    }

    /** @test */
    public function it_gets_the_package_name()
    {
        $this->assertEquals(
            'package-name',
            Addon::make('vendor-name/package-name')->packageName()
        );
    }

    /** @test */
    public function it_gets_the_edition()
    {
        $this->assertNull(Addon::make('foo/bar')->edition());

        config(['statamic.editions.addons.foo/bar' => 'test']);

        $this->assertEquals('test', Addon::make('foo/bar')->edition());
    }

    /** @test */
    public function it_creates_an_instance_from_a_package()
    {
        $addon = $this->makeFromPackage([]);

        $this->assertInstanceOf(Addon::class, $addon);
        $this->assertEquals('vendor/test-addon', $addon->id());
        $this->assertEquals('Test Addon', $addon->name());
        $this->assertEquals('Test description', $addon->description());
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
        $addon = Addon::make('Test Addon')->directory('/path/to/addon');

        File::shouldReceive('exists')->with('/path/to/addon/test.txt')->andReturnTrue();
        File::shouldReceive('exists')->with('/path/to/addon/notfound.txt')->andReturnFalse();

        $this->assertTrue($addon->hasFile('test.txt'));
        $this->assertFalse($addon->hasFile('notfound.txt'));
    }

    /** @test */
    public function it_gets_file_contents()
    {
        $addon = Addon::make('Test Addon')->directory('/path/to/addon');

        File::shouldReceive('get')->with('/path/to/addon/test.txt')->andReturn('the file contents');

        $this->assertEquals('the file contents', $addon->getFile('test.txt'));
    }

    /** @test */
    public function it_writes_file_contents()
    {
        $addon = Addon::make('Test Addon')->directory('/path/to/addon');

        File::shouldReceive('put')->with('/path/to/addon/test.txt', 'the file contents');

        $addon->putFile('test.txt', 'the file contents');
    }

    /** @test */
    public function it_doesnt_allow_getting_files_if_no_directory_is_set()
    {
        File::spy();
        $addon = $this->makeFromPackage(['directory' => null]);

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
    public function it_doesnt_allow_checking_for_files_if_no_directory_is_set()
    {
        File::spy();
        $addon = $this->makeFromPackage(['directory' => null]);

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
    public function it_doesnt_allow_writing_files_if_no_directory_is_set()
    {
        File::spy();
        $addon = $this->makeFromPackage(['directory' => null]);

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
        $addon = $this->makeFromPackage([
            'name' => null,
            'id' => 'BarBaz',
        ]);

        $this->assertEquals('BarBaz', $addon->name());
    }

    /** @test */
    public function it_checks_if_commercial()
    {
        $this->assertTrue($this->makeFromPackage(['isCommercial' => true])->isCommercial());
        $this->assertFalse($this->makeFromPackage(['isCommercial' => false])->isCommercial());
        $this->assertFalse($this->makeFromPackage([])->isCommercial());
    }

    /** @test */
    public function it_gets_the_license_key()
    {
        config(['test_addon' => ['license_key' => 'TESTLICENSEKEY']]);

        $this->assertEquals('TESTLICENSEKEY', Addon::make('vendor/test-addon')->licenseKey());
    }

    public function it_gets_the_autoloaded_directory()
    {
        $addon = $this->makeFromPackage(['autoload' => 'src']);

        $this->assertEquals('src', $addon->autoload());
    }

    private function makeFromPackage($attributes)
    {
        return Addon::makeFromPackage(array_merge([
            'id' => 'vendor/test-addon',
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
