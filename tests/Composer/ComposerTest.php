<?php

namespace Tests\Composer;

use Facades\Statamic\Composer\Composer;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Tests\Fakes\Composer\Package\PackToTheFuture;
use Tests\TestCase;

class ComposerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        Composer::swap(new \Statamic\Composer\Composer($this->basePath()));
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
    function it_can_get_installed_version_of_a_specific_package()
    {
        $this->assertEquals(app()->version(), Composer::installedVersion('laravel/framework'));
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
        $this->assertFalse(File::exists($this->basePath('vendor/test/package')));

        // Test that we can require the package...

        PackToTheFuture::setVersion('1.0.0');
        Composer::require('test/package');

        $installed = Composer::installed();
        $this->assertContains('test/package', $installed->keys());
        $this->assertTrue(File::exists($this->basePath('vendor/test/package')));
        $this->assertEquals('1.0.0', $installed->get('test/package')->version);

        // Test that we can update the package...

        PackToTheFuture::setVersion('1.0.1');
        Composer::update('test/package');

        $installed = Composer::installed();
        $this->assertContains('test/package', $installed->keys());
        $this->assertTrue(File::exists($this->basePath('vendor/test/package')));
        $this->assertEquals('1.0.1', $installed->get('test/package')->version);

        // Test that we can downgrade to a specific version...

        PackToTheFuture::setVersion('1.0.0');
        Composer::require('test/package', '1.0.0');

        $installed = Composer::installed();
        $this->assertContains('test/package', $installed->keys());
        $this->assertTrue(File::exists($this->basePath('vendor/test/package')));
        $this->assertEquals('1.0.0', $installed->get('test/package')->version);

        // Test that we can remove the package...

        Composer::remove('test/package');

        $this->assertNotContains('test/package', Composer::installed()->keys());
        $this->assertFalse(File::exists($this->basePath('vendor/test/package')));
    }

    private function basePath($path = null)
    {
        return __DIR__ . '/../../' . $path;
    }
}
