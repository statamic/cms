<?php

namespace Tests\StarterKits;

use Facades\Statamic\Console\Processes\Composer;
use Facades\Statamic\Console\Processes\TtyDetector;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;
use Statamic\Facades\Blink;
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

    /** @test */
    public function it_runs_post_install_hook_script()
    {
        $this->assertFileNotExists($this->kitVendorPath());
        $this->assertFileNotExists(base_path('copied.md'));

        $this->simulateCliInstallWithoutTtySupport();

        // Ensure starter kit itself was installed, but not yet cleaned up because a manual post-install is required
        $this->assertFileExists(base_path('copied.md'));
        $this->assertFileNotExists(base_path('composer.json.bak'));
        $this->assertFileExists(base_path('composer.json'));
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileExists($this->kitVendorPath());
        $this->assertFalse(Blink::has('post-install-hook-run'));

        $this->artisan('statamic:starter-kit:run-post-install', [
            'package' => 'statamic/cool-runnings',
        ]);

        // Now we should see that the hook has been run, and the starter kit has been cleaned up from vendor
        $this->assertTrue(Blink::has('post-install-hook-run'));
        $this->assertFileNotExists($this->kitVendorPath());
    }

    private function kitRepoPath($path = null)
    {
        return collect([base_path('repo/cool-runnings'), $path])->filter()->implode('/');
    }

    protected function kitVendorPath($path = null)
    {
        return collect([base_path('vendor/statamic/cool-runnings'), $path])->filter()->implode('/');
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
        Http::fake($customFake ?? [
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
