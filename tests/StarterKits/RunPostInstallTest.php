<?php

namespace Tests\StarterKits;

use Facades\Statamic\Console\Processes\Composer;
use Facades\Statamic\Console\Processes\TtyDetector;
use Facades\Statamic\StarterKits\Hook;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blink;
use Statamic\Facades\Path;
use Tests\Fakes\Composer\FakeComposer;
use Tests\TestCase;

class RunPostInstallTest extends TestCase
{
    use Concerns\BacksUpSite;

    protected $files;

    public function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);

        $this->restoreSite();
        $this->backupSite();
        $this->prepareRepo();

        Composer::swap(new FakeComposer($this));
    }

    public function tearDown(): void
    {
        $this->restoreSite();

        parent::tearDown();
    }

    #[Test]
    public function it_runs_post_install_hook_script()
    {
        $this->assertFileDoesNotExist($this->kitVendorPath());
        $this->assertFileDoesNotExist(base_path('copied.md'));

        $this->simulateCliInstallWithoutTtySupport();

        // Ensure starter kit itself was installed, but not yet cleaned up because a manual post-install is required
        $this->assertFileExists(base_path('copied.md'));
        $this->assertFileDoesNotExist(base_path('composer.json.bak'));
        $this->assertFileExists(base_path('composer.json'));
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileExists($this->kitVendorPath());
        $this->assertFileExists($this->kitVendorPath('StarterKitPostInstall.php'));
        $this->assertFalse(Blink::has('post-install-hook-run'));

        $this
            ->artisan('statamic:starter-kit:run-post-install', [
                'package' => 'statamic/cool-runnings',
            ])
            ->assertExitCode(0);

        // Now we should see that the hook has been run, and the starter kit has been cleaned up from vendor
        $this->assertTrue(Blink::has('post-install-hook-run'));
        $this->assertFileDoesNotExist($this->kitVendorPath());
    }

    #[Test]
    public function it_errors_gracefully_if_post_install_hook_cannot_be_found()
    {
        $this->simulateCliInstallWithoutTtySupport();

        Hook::shouldReceive('find')
            ->with($this->kitVendorPath('StarterKitPostInstall.php'))
            ->once()
            ->andReturn(false);

        $this
            ->artisan('statamic:starter-kit:run-post-install', [
                'package' => 'statamic/cool-runnings',
            ])
            ->expectsOutputToContain('Cannot find post-install hook for [statamic/cool-runnings].')
            ->assertExitCode(1);

        $this->assertFalse(Blink::has('post-install-hook-run'));
        $this->assertFileExists($this->kitVendorPath());
    }

    #[Test]
    public function it_errors_gracefully_if_starter_kit_package_doesnt_exist_in_vendor()
    {
        $this->simulateCliInstallWithoutTtySupport();

        Hook::shouldReceive('find')->never();

        $this
            ->artisan('statamic:starter-kit:run-post-install', [
                'package' => 'statamic/non-existent',
            ])
            ->expectsOutputToContain('Cannot find starter kit [statamic/non-existent] in vendor.')
            ->assertExitCode(1);

        $this->assertFalse(Blink::has('post-install-hook-run'));
        $this->assertFileExists($this->kitVendorPath());
    }

    private function kitRepoPath($path = null)
    {
        return Path::tidy(collect([base_path('repo/cool-runnings'), $path])->filter()->implode('/'));
    }

    protected function kitVendorPath($path = null)
    {
        return Path::tidy(collect([base_path('vendor/statamic/cool-runnings'), $path])->filter()->implode('/'));
    }

    private function prepareRepo()
    {
        $this->files->copyDirectory(__DIR__.'/__fixtures__/cool-runnings', $this->kitRepoPath());

        $this->files->copy(
            __DIR__.'/__fixtures__/cool-runnings-post-install-hook/StarterKitPostInstall.php',
            $this->kitRepoPath('StarterKitPostInstall.php')
        );
    }

    private function simulateCliInstallWithoutTtySupport()
    {
        Http::fake([
            'outpost.*' => Http::response(['data' => ['price' => null]], 200),
            'repo.packagist.org/*' => Http::response('', 200),
            '*' => Http::response('', 404),
        ]);

        // This is required to simulate no TTY in Windows,
        // so that starter kit doesn't get cleaned up on install
        TtyDetector::shouldReceive('isTtySupported')->andReturn(false);

        $this->artisan('statamic:starter-kit:install', array_merge([
            'package' => 'statamic/cool-runnings',
            '--no-interaction' => true,
            '--cli-install' => true, // This is also required to simulate cli installer, for same reason as above
        ]));
    }

    private function assertFileDoesntHaveContent($expected, $path)
    {
        $this->assertFileExists($path);

        $this->assertStringNotContainsString($expected, $this->files->get($path));
    }

    private function assertComposerJsonDoesntHave($package)
    {
        $this->assertFileDoesntHaveContent($package, base_path('composer.json'));
    }
}
