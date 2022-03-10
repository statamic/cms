<?php

namespace Statamic\Testing\Extend;

use Facades\Statamic\Licensing\LicenseManager;
use Foo\Bar\TestAddonServiceProvider;
use Illuminate\Support\Collection;
use Statamic\Extend\Addon;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Tests\TestCase;

class AddonTest extends TestCase
{
    protected $addonFixtureDir;

    public function setUp(): void
    {
        parent::setUp();
        $this->addonFixtureDir = Path::tidy(realpath(__DIR__.'/../Fixtures/Addon').'/');
    }

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
    public function it_gets_the_editions()
    {
        $addon = Addon::make('foo/bar');

        $this->assertInstanceOf(Collection::class, $addon->editions());
        $this->assertEquals([], $addon->editions()->all());
        $this->assertNull($addon->edition());

        $return = $addon->editions(['free', 'pro']);
        $this->assertEquals($addon, $return);

        $this->assertEquals(['free', 'pro'], $addon->editions()->all());
        $this->assertEquals('free', $addon->edition());

        config(['statamic.editions.addons.foo/bar' => 'pro']);
        $this->assertEquals('pro', $addon->edition());
    }

    /** @test */
    public function it_throws_exception_for_invalid_edition()
    {
        $this->expectExceptionMessage('Invalid edition [rad] for addon foo/bar');

        config(['statamic.editions.addons.foo/bar' => 'rad']);

        $this->makeFromPackage(['id' => 'foo/bar', 'editions' => []])->edition();
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
        $this->assertEquals($this->addonFixtureDir, $addon->directory());
        $this->assertEquals('', $addon->autoload());
        $this->assertEquals('http://test-url.com', $addon->url());
        $this->assertEquals('Test Developer LLC', $addon->developer());
        $this->assertEquals('http://test-developer.com', $addon->developerUrl());
        $this->assertEquals('1.0', $addon->version());
        $this->assertEquals(['foo', 'bar'], $addon->editions()->all());
    }

    /** @test */
    public function it_checks_if_a_file_exists()
    {
        $addon = $this->makeFromPackage();

        File::shouldReceive('exists')->with($this->addonFixtureDir.'test.txt')->andReturnTrue();
        File::shouldReceive('exists')->with($this->addonFixtureDir.'notfound.txt')->andReturnFalse();

        $this->assertTrue($addon->hasFile('test.txt'));
        $this->assertFalse($addon->hasFile('notfound.txt'));
    }

    /** @test */
    public function it_gets_file_contents()
    {
        $addon = $this->makeFromPackage();

        File::shouldReceive('get')->with($this->addonFixtureDir.'test.txt')->andReturn('the file contents');

        $this->assertEquals('the file contents', $addon->getFile('test.txt'));
    }

    /** @test */
    public function it_writes_file_contents()
    {
        $addon = $this->makeFromPackage();

        File::shouldReceive('put')->with($this->addonFixtureDir.'test.txt', 'the file contents');

        $addon->putFile('test.txt', 'the file contents');
    }

    /** @test */
    public function it_doesnt_allow_getting_files_if_no_provider_is_set()
    {
        File::spy();
        $addon = $this->makeFromPackage(['provider' => null]);

        try {
            $addon->getFile('foo.txt', 'foo');
        } catch (\Exception $e) {
            $this->assertEquals('Cannot get files without a provider specified.', $e->getMessage());
            File::shouldNotHaveReceived('get');

            return;
        }

        $this->fail('Exception was not thrown.');
    }

    /** @test */
    public function it_doesnt_allow_checking_for_files_if_no_provider_is_set()
    {
        File::spy();
        $addon = $this->makeFromPackage(['provider' => null]);

        try {
            $addon->hasFile('foo.txt', 'foo');
        } catch (\Exception $e) {
            $this->assertEquals('Cannot check files without a provider specified.', $e->getMessage());
            File::shouldNotHaveReceived('get');

            return;
        }

        $this->fail('Exception was not thrown.');
    }

    /** @test */
    public function it_doesnt_allow_writing_files_if_no_provider_is_set()
    {
        File::spy();
        $addon = $this->makeFromPackage(['provider' => null]);

        try {
            $addon->putFile('foo.txt', 'foo');
        } catch (\Exception $e) {
            $this->assertEquals('Cannot write files without a provider specified.', $e->getMessage());
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

    public function it_gets_the_autoloaded_directory()
    {
        $addon = $this->makeFromPackage(['autoload' => 'src']);

        $this->assertEquals('src', $addon->autoload());
    }

    /** @test */
    public function it_gets_the_license()
    {
        LicenseManager::shouldReceive('addons')->once()->andReturn(collect([
            'foo/bar' => 'the license',
        ]));

        $this->assertEquals('the license', Addon::make('foo/bar')->license());
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
