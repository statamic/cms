<?php

namespace Tests\Composer;

use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Facades\Statamic\Console\Processes\Composer;
use Tests\Fakes\Composer\Package\PackToTheFuture;

class ComposerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Composer::swap(new \Statamic\Console\Processes\Composer($this->basePath()));

        $this->ensureTestPackageNotInstalled();
    }

    public function tearDown(): void
    {
        $this->ensureTestPackageNotInstalled();

        parent::tearDown();
    }

    /**
     * @group integration
     * @test
     */
    function it_can_list_installed_packages_with_details()
    {
        $installed = Composer::installed();

        $this->assertNotEmpty($installed);
        $this->assertContains('laravel/framework', $installed->keys());
        $this->assertEquals(app()->version(), $installed->get('laravel/framework')->version);
    }

    /**
     * @group integration
     * @test
     */
    function it_can_get_installed_version_of_a_package_directly_from_composer_lock()
    {
        $this->assertEquals(app()->version(), Composer::installedVersion('laravel/framework'));
    }

    /**
     * @group integration
     * @test
     */
    function it_can_get_installed_path_of_a_package()
    {
        $this->assertContains('/vendor/laravel/framework', Composer::installedPath('laravel/framework'));
    }

    /**
     * This method is intentionally doing way too much, for the sake of test suite performance.
     *
     * @group integration
     * @group slow
     * @test
     */
    function it_can_require_update_downgrade_and_remove_a_package()
    {
        // Test that the package isn't installed yet...

        $this->assertNotContains('test/package', Composer::installed()->keys());
        $this->assertFileNotExists($this->basePath('vendor/test/package'));
        $this->assertFalse(Cache::has('composer.test/package'));

        // Test that we can require the package...

        PackToTheFuture::setVersion('1.0.0');
        Composer::require('test/package');

        $installed = Composer::installed();
        $this->assertContains('test/package', $installed->keys());
        $this->assertFileExists($this->basePath('vendor/test/package'));
        $this->assertEquals('1.0.0', $installed->get('test/package')->version);
        $this->assertTrue(str_contains(Cache::get('composer.test/package')['output'], 'Installing test/package'));

        // Test that we can update the package...

        PackToTheFuture::setVersion('1.0.1');
        Composer::update('test/package');

        $installed = Composer::installed();
        $this->assertContains('test/package', $installed->keys());
        $this->assertFileExists($this->basePath('vendor/test/package'));
        $this->assertEquals('1.0.1', $installed->get('test/package')->version);
        $this->assertTrue(str_contains(Cache::get('composer.test/package')['output'], 'Updating test/package'));

        // Test that we can downgrade to a specific version...

        PackToTheFuture::setVersion('1.0.0');
        Composer::require('test/package', '1.0.0');

        $installed = Composer::installed();
        $this->assertContains('test/package', $installed->keys());
        $this->assertFileExists($this->basePath('vendor/test/package'));
        $this->assertEquals('1.0.0', $installed->get('test/package')->version);
        $this->assertTrue(str_contains(Cache::get('composer.test/package')['output'], 'Downgrading test/package'));

        // Test that we can remove the package...

        Composer::remove('test/package');

        $this->assertNotContains('test/package', Composer::installed()->keys());
        $this->assertFileNotExists($this->basePath('vendor/test/package'));
        $this->assertTrue(str_contains(Cache::get('composer.test/package')['output'], 'Removing test/package'));
    }

    private function basePath($path = null)
    {
        return __DIR__ . '/../../' . $path;
    }

    private function ensureTestPackageNotInstalled()
    {
        Cache::forget('composer.test/package');

        if (Composer::installed()->get('test/package')) {
            Composer::remove('test/package');
        }
    }
}
