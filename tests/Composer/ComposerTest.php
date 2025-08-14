<?php

namespace Tests\Composer;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Process\Process;
use Tests\Fakes\Composer\Package\PackToTheFuture;
use Tests\TestCase;

class ComposerTest extends TestCase
{
    private $files;

    public function setUp(): void
    {
        $this->markTestSkippedInWindows();

        parent::setUp();

        (new Process(['tar', '-xzvf', 'vendor.tar.gz'], $this->basePath()))->mustRun();
        copy($this->basePath('composer.json'), $this->basePath('composer.json.bak'));
        copy($this->basePath('composer.lock'), $this->basePath('composer.lock.bak'));
        Cache::forget('composer.test/package');

        Composer::swap(new \Statamic\Console\Processes\Composer($this->basePath()));

        $this->files = app('files');

        if (! $this->files->exists($tmpDir = $this->basePath('tmp'))) {
            $this->files->makeDirectory($tmpDir, 0755, true);
        }
    }

    public function tearDown(): void
    {
        // If the test was skipped, avoid trying to clean up. The setUp would've never happened.
        if (! $this->files) {
            parent::tearDown();

            return;
        }

        $this->files->deleteDirectory($this->basePath('tmp'));
        $this->files->deleteDirectory($this->basePath('vendor'));
        $this->files->delete($this->basePath('composer.json'));
        $this->files->delete($this->basePath('composer.lock'));
        $this->files->move($this->basePath('composer.lock.bak'), $this->basePath('composer.lock'));
        $this->files->move($this->basePath('composer.json.bak'), $this->basePath('composer.json'));

        parent::tearDown();
    }

    #[Group('integration')]
    #[Test]
    public function it_can_list_installed_packages_with_details()
    {
        $installed = Composer::installed();

        $this->assertNotEmpty($installed);

        $this->assertContains('statamic/composer-test-example-dependency', $installed->keys());
        $this->assertEquals('1.2.3', $installed->get('statamic/composer-test-example-dependency')->version);
        $this->assertFalse($installed->get('statamic/composer-test-example-dependency')->dev);

        $this->assertContains('statamic/composer-test-example-dev-dependency', $installed->keys());
        $this->assertEquals('1.2.4', $installed->get('statamic/composer-test-example-dev-dependency')->version);
        $this->assertTrue($installed->get('statamic/composer-test-example-dev-dependency')->dev);
    }

    #[Group('integration')]
    #[Test]
    public function it_can_get_installed_version_of_a_package_directly_from_composer_lock()
    {
        $this->assertEquals('1.2.3', Composer::installedVersion('statamic/composer-test-example-dependency'));
    }

    #[Group('integration')]
    #[Test]
    public function it_can_check_if_package_is_installed()
    {
        $this->assertTrue(Composer::isInstalled('statamic/composer-test-example-dependency'));
        $this->assertFalse(Composer::isInstalled('statamic/another-dependency'));
    }

    #[Group('integration')]
    #[Test]
    public function it_can_get_installed_path_of_a_package()
    {
        $this->assertEquals(
            __DIR__.'/__fixtures__/vendor/statamic/composer-test-example-dependency',
            Composer::installedPath('statamic/composer-test-example-dependency')
        );

        $this->assertEquals(
            __DIR__.'/__fixtures__/vendor/composer/composer',
            Composer::installedPath('composer/composer')
        );
    }

    #[Group('integration')]
    #[Test]
    public function it_gracefully_fails_when_lock_file_does_not_exist()
    {
        unlink($this->basePath('composer.lock'));

        $installed = Composer::installed();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $installed);
        $this->assertEmpty($installed);
        $this->assertNull(Composer::installedVersion('statamic/composer-test-example-dependency'));
    }

    #[Group('integration')]
    #[Group('slow')]
    #[Test]
    /**
     * This method is intentionally doing way too much, for the sake of test suite performance.
     */
    public function it_can_require_update_downgrade_and_remove_a_package()
    {
        // Test that the package isn't installed yet...

        $this->assertNotContains('test/package', Composer::installed()->keys());
        $this->assertFileDoesNotExist($this->basePath('vendor/test/package'));
        $this->assertFalse(Cache::has('composer.test/package'));

        // Test that we can require a package...

        PackToTheFuture::setVersion('1.0.0');
        Composer::require('test/package');

        $installed = Composer::installed();
        $this->assertTrue($installed->keys()->contains('test/package'));
        $this->assertFileExists($this->basePath('vendor/test/package'));
        $this->assertEquals('1.0.0', $installed->get('test/package')->version);
        $this->assertFalse($installed->get('test/package')->dev);
        $this->assertStringContainsString('Installing test/package', Cache::get('composer.test/package')['output']);

        // Test that we can update the package...

        PackToTheFuture::setVersion('1.0.1');
        Composer::update('test/package');

        $installed = Composer::installed();
        $this->assertTrue($installed->keys()->contains('test/package'));
        $this->assertFileExists($this->basePath('vendor/test/package'));
        $this->assertEquals('1.0.1', $installed->get('test/package')->version);
        $this->assertFalse($installed->get('test/package')->dev);
        $this->assertStringContainsString('Upgrading test/package', Cache::get('composer.test/package')['output']);

        // Test that we can downgrade to a specific version...

        PackToTheFuture::setVersion('1.0.0');
        Composer::require('test/package', '1.0.0');

        $installed = Composer::installed();
        $this->assertTrue($installed->keys()->contains('test/package'));
        $this->assertFileExists($this->basePath('vendor/test/package'));
        $this->assertEquals('1.0.0', $installed->get('test/package')->version);
        $this->assertFalse($installed->get('test/package')->dev);
        $this->assertStringContainsString('Downgrading test/package', Cache::get('composer.test/package')['output']);

        // Test that we can remove the package...

        Composer::remove('test/package');

        $this->assertStringNotContainsString('test/package', Composer::installed()->keys());
        $this->assertFileDoesNotExist($this->basePath('vendor/test/package'));
        $this->assertStringContainsString('Removing test/package', Cache::get('composer.test/package')['output']);

        // Test that we can add extra params when requiring...

        PackToTheFuture::setVersion('1.0.0');
        Composer::require('test/package', '1.0.0', '--dry-run');

        $installed = Composer::installed();
        $this->assertFalse($installed->keys()->contains('test/package'));
        $this->assertFileDoesNotExist($this->basePath('vendor/test/package'));
        $this->assertStringContainsString('Installing test/package', Cache::get('composer.test/package')['output']);

        // Test that we can add extra params when requiring a dev dependency...

        PackToTheFuture::setVersion('1.0.0');
        Composer::requireDev('test/package', '1.0.0', '--dry-run');

        $installed = Composer::installed();
        $this->assertFalse($installed->keys()->contains('test/package'));
        $this->assertFileDoesNotExist($this->basePath('vendor/test/package'));
        $this->assertStringContainsString('Installing test/package', Cache::get('composer.test/package')['output']);

        // Test that we can require a package as a dev dependency...

        PackToTheFuture::setVersion('1.0.0');
        Composer::requireDev('test/package');

        $installed = Composer::installed();
        $this->assertTrue($installed->keys()->contains('test/package'));
        $this->assertFileExists($this->basePath('vendor/test/package'));
        $this->assertEquals('1.0.0', $installed->get('test/package')->version);
        $this->assertTrue($installed->get('test/package')->dev);
        $this->assertStringContainsString('Installing test/package', Cache::get('composer.test/package')['output']);

        // Test that we can remove a dev package...

        Composer::removeDev('test/package');

        $this->assertStringNotContainsString('test/package', Composer::installed()->keys());
        $this->assertFileDoesNotExist($this->basePath('vendor/test/package'));
        $this->assertStringContainsString('Removing test/package', Cache::get('composer.test/package')['output']);
    }

    #[Group('integration')]
    #[Group('slow')]
    #[Test]
    /**
     * This method is intentionally doing way too much, for the sake of test suite performance.
     */
    public function it_can_require_and_remove_multiple_packages_in_one_shot()
    {
        PackToTheFuture::generateComposerJson('test/one', '1.0.0', [], $this->basePath('tmp/one/composer.json'));
        PackToTheFuture::generateComposerJson('test/two', '2.0.0', [], $this->basePath('tmp/two/composer.json'));

        $repositories = [
            'require' => [
                'composer/composer' => '^2.0.0',
            ],
            'repositories' => [
                ['type' => 'path', 'url' => $this->basePath('tmp/one'), 'options' => ['symlink' => false]],
                ['type' => 'path', 'url' => $this->basePath('tmp/two'), 'options' => ['symlink' => false]],
            ],
        ];

        PackToTheFuture::generateComposerJson('statamic/composer-test-app', '1.0.0', $repositories, $this->basePath('composer.json'));

        // Test that the packages aren't installed yet...

        $this->assertNotContains('test/one', Composer::installed()->keys());
        $this->assertNotContains('test/two', Composer::installed()->keys());
        $this->assertFileDoesNotExist($this->basePath('vendor/test/one'));
        $this->assertFileDoesNotExist($this->basePath('vendor/test/two'));

        // Test that we can require multiple packages...

        Composer::requireMultiple([
            'test/one',
            'test/two' => '2.0.0', // Test it can require explicit version.
        ]);

        $installed = Composer::installed();
        $output = Cache::get('composer.test/one')['output'];
        $this->assertTrue($installed->keys()->contains('test/one'));
        $this->assertTrue($installed->keys()->contains('test/two'));
        $this->assertFileExists($this->basePath('vendor/test/one'));
        $this->assertFileExists($this->basePath('vendor/test/two'));
        $this->assertEquals('1.0.0', $installed->get('test/one')->version);
        $this->assertEquals('2.0.0', $installed->get('test/two')->version);
        $this->assertFalse($installed->get('test/one')->dev);
        $this->assertFalse($installed->get('test/two')->dev);
        $this->assertStringContainsString('Installing test/one', $output);
        $this->assertStringContainsString('Installing test/two', $output);

        // Test that we can remove multiple packages...

        Composer::removeMultiple(['test/one', 'test/two']);

        $output = Cache::get('composer.test/one')['output'];
        $this->assertStringNotContainsString('test/one', Composer::installed()->keys());
        $this->assertStringNotContainsString('test/two', Composer::installed()->keys());
        $this->assertFileDoesNotExist($this->basePath('vendor/test/one'));
        $this->assertFileDoesNotExist($this->basePath('vendor/test/two'));
        $this->assertStringContainsString('Removing test/one', $output);
        $this->assertStringContainsString('Removing test/two', $output);

        // Test that we can add extra params when requiring...

        Composer::requireMultiple(['test/one', 'test/two'], '--dry-run');

        $output = Cache::get('composer.test/one')['output'];
        $this->assertStringNotContainsString('test/one', Composer::installed()->keys());
        $this->assertStringNotContainsString('test/two', Composer::installed()->keys());
        $this->assertFileDoesNotExist($this->basePath('vendor/test/one'));
        $this->assertFileDoesNotExist($this->basePath('vendor/test/two'));
        $this->assertStringContainsString('Installing test/one', $output);
        $this->assertStringContainsString('Installing test/two', $output);

        // Test that we can add extra params when requiring dev dependencies...

        Composer::requireMultipleDev(['test/one', 'test/two'], '--dry-run');

        $output = Cache::get('composer.test/one')['output'];
        $this->assertStringNotContainsString('test/one', Composer::installed()->keys());
        $this->assertStringNotContainsString('test/two', Composer::installed()->keys());
        $this->assertFileDoesNotExist($this->basePath('vendor/test/one'));
        $this->assertFileDoesNotExist($this->basePath('vendor/test/two'));
        $this->assertStringContainsString('Installing test/one', $output);
        $this->assertStringContainsString('Installing test/two', $output);

        // Test that we can require multiple packages as dev dependencies...

        Composer::requireMultipleDev([
            'test/one',
            'test/two' => '2.0.0', // Test it can require explicit version.
        ]);

        $installed = Composer::installed();
        $output = Cache::get('composer.test/one')['output'];
        $this->assertTrue($installed->keys()->contains('test/one'));
        $this->assertTrue($installed->keys()->contains('test/two'));
        $this->assertFileExists($this->basePath('vendor/test/one'));
        $this->assertFileExists($this->basePath('vendor/test/two'));
        $this->assertEquals('1.0.0', $installed->get('test/one')->version);
        $this->assertEquals('2.0.0', $installed->get('test/two')->version);
        $this->assertTrue($installed->get('test/one')->dev);
        $this->assertTrue($installed->get('test/two')->dev);
        $this->assertStringContainsString('Installing test/one', $output);
        $this->assertStringContainsString('Installing test/two', $output);

        // Test that we can remove multiple dev dependencies...

        Composer::removeMultipleDev(['test/one', 'test/two']);

        $output = Cache::get('composer.test/one')['output'];
        $this->assertStringNotContainsString('test/one', Composer::installed()->keys());
        $this->assertStringNotContainsString('test/two', Composer::installed()->keys());
        $this->assertFileDoesNotExist($this->basePath('vendor/test/one'));
        $this->assertFileDoesNotExist($this->basePath('vendor/test/two'));
        $this->assertStringContainsString('Removing test/one', $output);
        $this->assertStringContainsString('Removing test/two', $output);
    }

    private function basePath($path = null)
    {
        return __DIR__.'/__fixtures__/'.$path;
    }
}
