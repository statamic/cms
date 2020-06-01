<?php

namespace Tests\Composer;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Support\Facades\Cache;
use Statamic\Support\Str;
use Symfony\Component\Process\Process;
use Tests\Fakes\Composer\Package\PackToTheFuture;
use Tests\TestCase;

class ComposerTest extends TestCase
{
    public function setUp(): void
    {
        if ($this->isRunningWindows()) {
            $this->markTestSkipped();
        }

        parent::setUp();

        (new Process(['tar', '-xzvf', 'vendor.tar.gz'], $this->basePath()))->mustRun();
        copy($this->basePath('composer.json'), $this->basePath('composer.json.bak'));
        copy($this->basePath('composer.lock'), $this->basePath('composer.lock.bak'));
        Cache::forget('composer.test/package');

        Composer::swap(new \Statamic\Console\Processes\Composer($this->basePath()));
    }

    public function tearDown(): void
    {
        $fs = app('files');
        $fs->deleteDirectory($this->basePath('vendor'));
        $fs->delete($this->basePath('composer.json'));
        $fs->delete($this->basePath('composer.lock'));
        $fs->move($this->basePath('composer.lock.bak'), $this->basePath('composer.lock'));
        $fs->move($this->basePath('composer.json.bak'), $this->basePath('composer.json'));

        parent::tearDown();
    }

    /**
     * @group integration
     * @test
     */
    public function it_can_list_installed_packages_with_details()
    {
        $installed = Composer::installed();

        $this->assertNotEmpty($installed);
        $this->assertContains('statamic/composer-test-example-dependency', $installed->keys());
        $this->assertEquals('1.2.3', $installed->get('statamic/composer-test-example-dependency')->version);
    }

    /**
     * @group integration
     * @test
     */
    public function it_can_get_installed_version_of_a_package_directly_from_composer_lock()
    {
        $this->assertEquals('1.2.3', Composer::installedVersion('statamic/composer-test-example-dependency'));
    }

    /**
     * @group integration
     * @test
     */
    public function it_can_get_installed_path_of_a_package()
    {
        $this->assertEquals(
            __DIR__.'/__fixtures__/example-dependency',
            Composer::installedPath('statamic/composer-test-example-dependency')
        );

        $this->assertEquals(
            __DIR__.'/__fixtures__/vendor/composer/composer',
            Composer::installedPath('composer/composer')
        );
    }

    /**
     * This method is intentionally doing way too much, for the sake of test suite performance.
     *
     * @group integration
     * @group slow
     * @test
     */
    public function it_can_require_update_downgrade_and_remove_a_package()
    {
        // Test that the package isn't installed yet...

        $this->assertNotContains('test/package', Composer::installed()->keys());
        $this->assertFileNotExists($this->basePath('vendor/test/package'));
        $this->assertFalse(Cache::has('composer.test/package'));

        // Test that we can require the package...

        PackToTheFuture::setVersion('1.0.0');
        Composer::require('test/package');

        $installed = Composer::installed();
        $this->assertTrue($installed->keys()->contains('test/package'));
        $this->assertFileExists($this->basePath('vendor/test/package'));
        $this->assertEquals('1.0.0', $installed->get('test/package')->version);
        $this->assertTrue(Str::contains(Cache::get('composer.test/package')['output'], 'Installing test/package'));

        // Test that we can update the package...

        PackToTheFuture::setVersion('1.0.1');
        Composer::update('test/package');

        $installed = Composer::installed();
        $this->assertTrue($installed->keys()->contains('test/package'));
        $this->assertFileExists($this->basePath('vendor/test/package'));
        $this->assertEquals('1.0.1', $installed->get('test/package')->version);
        $this->assertTrue(Str::contains(Cache::get('composer.test/package')['output'], 'Updating test/package'));

        // Test that we can downgrade to a specific version...

        PackToTheFuture::setVersion('1.0.0');
        Composer::require('test/package', '1.0.0');

        $installed = Composer::installed();
        $this->assertTrue($installed->keys()->contains('test/package'));
        $this->assertFileExists($this->basePath('vendor/test/package'));
        $this->assertEquals('1.0.0', $installed->get('test/package')->version);
        $this->assertTrue(Str::contains(Cache::get('composer.test/package')['output'], 'Downgrading test/package'));

        // Test that we can remove the package...

        Composer::remove('test/package');

        $this->assertStringNotContainsString('test/package', Composer::installed()->keys());
        $this->assertFileNotExists($this->basePath('vendor/test/package'));
        $this->assertTrue(Str::contains(Cache::get('composer.test/package')['output'], 'Removing test/package'));
    }

    private function basePath($path = null)
    {
        return __DIR__.'/__fixtures__/'.$path;
    }
}
