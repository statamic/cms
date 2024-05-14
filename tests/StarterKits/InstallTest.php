<?php

namespace Tests\StarterKits;

use Facades\Statamic\Console\Processes\Composer;
use Facades\Statamic\Console\Processes\TtyDetector;
use Facades\Statamic\StarterKits\Hook;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;
use Mockery;
use Statamic\Console\Commands\StarterKitInstall as InstallCommand;
use Statamic\Facades\Blink;
use Statamic\Facades\Config;
use Statamic\Facades\YAML;
use Statamic\Support\Str;
use Tests\Fakes\Composer\FakeComposer;
use Tests\TestCase;

class InstallTest extends TestCase
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
    public function it_installs_starter_kit()
    {
        $this->assertFileDoesNotExist($this->kitVendorPath());
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileDoesNotExist(base_path('copied.md'));

        $this->installCoolRunnings();

        $this->assertFalse(Blink::has('starter-kit-repository-added'));
        $this->assertFileDoesNotExist($this->kitVendorPath());
        $this->assertFileDoesNotExist(base_path('composer.json.bak'));
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileExists(base_path('copied.md'));
    }

    /** @test */
    public function it_installs_from_custom_export_paths()
    {
        $this->setConfig([
            'export_paths' => [
                'config',
                'copied.md',
            ],
            'export_as' => [
                'README.md' => 'README-for-new-site.md',
                'original-dir' => 'renamed-dir',
            ],
        ]);

        $this->assertFileDoesNotExist($this->kitVendorPath());
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileDoesNotExist(base_path('copied.md'));
        $this->assertFileDoesNotExist($renamedFile = base_path('README.md'));
        $this->assertFileDoesNotExist($renamedFolder = base_path('original-dir'));

        $this->installCoolRunnings();

        $this->assertFalse(Blink::has('starter-kit-repository-added'));
        $this->assertFileDoesNotExist($this->kitVendorPath());
        $this->assertFileDoesNotExist(base_path('composer.json.bak'));
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileExists(base_path('copied.md'));
        $this->assertFileExists($renamedFile);
        $this->assertFileExists($renamedFolder);

        $this->assertFileDoesNotExist(base_path('README-for-new-site.md')); // This was renamed back to original path on install
        $this->assertFileDoesNotExist(base_path('renamed-dir')); // This was renamed back to original path on install

        $this->assertFileHasContent('This readme should get installed to README.md.', $renamedFile);
        $this->assertFileHasContent('One.', $renamedFolder.'/one.txt');
        $this->assertFileHasContent('Two.', $renamedFolder.'/two.txt');
    }

    /** @test */
    public function it_installs_from_github()
    {
        $this->assertFileDoesNotExist($this->kitVendorPath());
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileDoesNotExist(base_path('copied.md'));

        $this->installCoolRunnings([], [
            'outpost.*' => Http::response(['data' => ['price' => null]], 200),
            'github.com/*' => Http::response('', 200),
            '*' => Http::response('', 404),
        ]);

        $this->assertEquals('https://github.com/statamic/cool-runnings', Blink::get('starter-kit-repository-added'));
        $this->assertFileDoesNotExist($this->kitVendorPath());
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileExists(base_path('copied.md'));
    }

    /** @test */
    public function it_installs_from_bitbucket()
    {
        $this->assertFileDoesNotExist($this->kitVendorPath());
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileDoesNotExist(base_path('copied.md'));

        $this->installCoolRunnings([], [
            'outpost.*' => Http::response(['data' => ['price' => null]], 200),
            'bitbucket.org/*' => Http::response('', 200),
            '*' => Http::response('', 404),
        ]);

        $this->assertEquals('https://bitbucket.org/statamic/cool-runnings.git', Blink::get('starter-kit-repository-added'));
        $this->assertFileDoesNotExist($this->kitVendorPath());
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileExists(base_path('copied.md'));
    }

    /** @test */
    public function it_installs_from_gitlab()
    {
        $this->assertFileDoesNotExist($this->kitVendorPath());
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileDoesNotExist(base_path('copied.md'));

        $this->installCoolRunnings([], [
            'outpost.*' => Http::response(['data' => ['price' => null]], 200),
            'gitlab.com/*' => Http::response('', 200),
            '*' => Http::response('', 404),
        ]);

        $this->assertEquals('https://gitlab.com/statamic/cool-runnings', Blink::get('starter-kit-repository-added'));
        $this->assertFileDoesNotExist($this->kitVendorPath());
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileExists(base_path('copied.md'));
    }

    /** @test */
    public function it_installs_successfully_without_pinging_cloud_when_local_option_is_passed()
    {
        Http::fake(function ($request) {
            return Str::contains($request->url(), 'outpost.')
                ? Http::response(['data' => ['price' => null]], 200)
                : $this->fail('We should not be checking cloud for repo when passing `--local` option.');
        });

        $this->installCoolRunnings(['--local' => true]);

        $this->assertFileExists(base_path('copied.md'));
    }

    /** @test */
    public function it_restores_existing_repositories_after_successful_install()
    {
        $this->assertFileDoesNotExist($this->kitVendorPath());
        $this->assertFileDoesNotExist(base_path('copied.md'));

        $composerJson = json_decode($this->files->get(base_path('composer.json')), true);

        $expectedRepositories = $composerJson['repositories'] = [
            [
                'type' => 'path',
                'path' => '/some/path',
            ],
            [
                'type' => 'vcs',
                'url' => 'https://example.com/some/url',
            ],
        ];

        $this->files->put(
            base_path('composer.json'),
            json_encode($composerJson, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        );

        $this->installCoolRunnings([], [
            'outpost.*' => Http::response(['data' => ['price' => null]], 200),
            'github.com/*' => Http::response('', 200),
            '*' => Http::response('', 404),
        ]);

        $this->assertEquals('https://github.com/statamic/cool-runnings', Blink::get('starter-kit-repository-added'));
        $this->assertFileDoesNotExist($this->kitVendorPath());
        $this->assertFileExists(base_path('copied.md'));

        $composerJson = json_decode($this->files->get(base_path('composer.json')), true);

        $this->assertCount(2, $composerJson['repositories']);
        $this->assertEquals($expectedRepositories, $composerJson['repositories']);
    }

    /** @test */
    public function it_fails_if_starter_kit_config_does_not_exist()
    {
        $this->files->delete($this->kitRepoPath('starter-kit.yaml'));

        $this->installCoolRunnings();

        $this->assertFileDoesNotExist(base_path('copied.md'));
    }

    /** @test */
    public function it_fails_if_an_export_path_doesnt_exist()
    {
        $this->setConfig([
            'export_paths' => [
                'config',
                'does_not_exist',
            ],
        ]);

        $this->installCoolRunnings();

        $this->assertFileDoesNotExist(base_path('copied.md'));
    }

    /** @test */
    public function it_merges_folders()
    {
        $this->files->put($this->preparePath(base_path('content/collections/pages/contact.md')), 'Contact');

        $this->assertFileExists(base_path('content/collections/pages/contact.md'));
        $this->assertFileDoesNotExist(base_path('content/collections/pages/home.md'));

        $this->installCoolRunnings();

        $this->assertFileExists(base_path('content/collections/pages/contact.md'));
        $this->assertFileExists(base_path('content/collections/pages/home.md'));
    }

    /** @test */
    public function it_doesnt_copy_files_not_defined_as_export_paths()
    {
        $this->assertFileDoesNotExist(base_path('copied.md'));
        $this->assertFileDoesNotExist(base_path('not-copied.md'));

        $this->installCoolRunnings();

        $this->assertFileExists(base_path('copied.md'));
        $this->assertFileDoesNotExist(base_path('not-copied.md'));
    }

    /** @test */
    public function it_overwrites_files()
    {
        $this->assertFileExists(config_path('filesystems.php'));
        $this->assertFileDoesntHaveContent('bobsled_pics', config_path('filesystems.php'));

        $this->installCoolRunnings();

        $this->assertFileHasContent('bobsled_pics', config_path('filesystems.php'));
    }

    /** @test */
    public function it_doesnt_copy_starter_kit_config_by_default()
    {
        $this->installCoolRunnings();

        $this->assertFileDoesNotExist(base_path('starter-kit.yaml'));
    }

    /** @test */
    public function it_copies_starter_kit_config_when_option_is_passed()
    {
        $this->installCoolRunnings(['--with-config' => true]);

        $this->assertFileExists($configPath = base_path('starter-kit.yaml'));

        $expected = <<<"EOT"
export_paths:
  - config
  - content
  - resources
  - copied.md\n
EOT;

        $this->assertEquals($expected, $this->files->get($configPath));
    }

    /** @test */
    public function it_copies_starter_kit_post_install_script_hook_when_with_config_option_is_passed()
    {
        $this->files->put($this->kitRepoPath('StarterKitPostInstall.php'), '<?php');

        Hook::shouldReceive('find')
            ->with($this->kitVendorPath('StarterKitPostInstall.php'))
            ->once()
            ->andReturn(null);

        $this->installCoolRunnings(['--with-config' => true]);

        $this->assertFileExists($hookPath = base_path('StarterKitPostInstall.php'));
        $this->assertFileHasContent('<?php', $hookPath);
    }

    /** @test */
    public function it_doesnt_copy_starter_kit_post_install_script_hook_when_with_config_option_is_not_passed()
    {
        $this->files->put($this->kitRepoPath('StarterKitPostInstall.php'), '<?php');

        Hook::shouldReceive('find')
            ->with($this->kitVendorPath('StarterKitPostInstall.php'))
            ->once()
            ->andReturn(null);

        $this->installCoolRunnings();

        $this->assertFileDoesNotExist(base_path('StarterKitPostInstall.php'));
    }

    /** @test */
    public function it_overwrites_starter_kit_config_when_option_is_passed()
    {
        $this->files->put($configPath = base_path('starter-kit.yaml'), 'old config');

        $this->installCoolRunnings(['--with-config' => true]);

        $expected = <<<"EOT"
export_paths:
  - config
  - content
  - resources
  - copied.md\n
EOT;

        $this->assertEquals($expected, $this->files->get($configPath));
    }

    /** @test */
    public function it_doesnt_clear_site_by_default()
    {
        $this->files->put($this->preparePath(base_path('content/collections/pages/contact.md')), 'Contact');
        $this->files->put($this->preparePath(base_path('content/collections/blog/article.md')), 'Article');

        $this->installCoolRunnings();

        $this->assertFileExists(base_path('content/collections/pages/home.md'));
        $this->assertFileExists(base_path('content/collections/pages/contact.md'));
        $this->assertFileExists(base_path('content/collections/blog/article.md'));
    }

    /** @test */
    public function it_clears_site_when_option_is_passed()
    {
        $this->files->put($this->preparePath(base_path('content/collections/pages/contact.md')), 'Contact');
        $this->files->put($this->preparePath(base_path('content/collections/blog/article.md')), 'Article');

        $this->installCoolRunnings(['--clear-site' => true]);

        $this->assertFileExists(base_path('content/collections/pages/home.md'));
        $this->assertFileDoesNotExist(base_path('content/collections/pages/contact.md'));
        $this->assertFileDoesNotExist(base_path('content/collections/blog'));
    }

    /** @test */
    public function it_installs_dependencies()
    {
        $this->setConfig([
            'export_paths' => [
                'config',
            ],
            'dependencies' => [
                'statamic/seo-pro' => '^0.2.0',
                'bobsled/speed-calculator' => '^1.0.0',
            ],
        ]);

        $this->assertFileDoesNotExist(base_path('vendor/statamic/cool-runnings'));
        $this->assertFileDoesNotExist(base_path('vendor/statamic/seo-pro'));
        $this->assertComposerJsonDoesntHave('statamic/seo-pro');
        $this->assertFileDoesNotExist(base_path('vendor/bobsled/speed-calculator'));
        $this->assertComposerJsonDoesntHave('bobsled/speed-calculator');

        $this->installCoolRunnings();

        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileDoesNotExist(base_path('vendor/statamic/cool-runnings'));
        $this->assertFileExists(base_path('vendor/statamic/seo-pro'));
        $this->assertComposerJsonHasPackageVersion('require', 'statamic/seo-pro', '^0.2.0');
        $this->assertFileExists(base_path('vendor/bobsled/speed-calculator'));
        $this->assertComposerJsonHasPackageVersion('require', 'bobsled/speed-calculator', '^1.0.0');
    }

    /** @test */
    public function it_installs_dev_dependencies()
    {
        $this->setConfig([
            'export_paths' => [
                'config',
            ],
            'dependencies_dev' => [
                'statamic/ssg' => '*',
            ],
        ]);

        $this->assertFileDoesNotExist(base_path('vendor/statamic/cool-runnings'));
        $this->assertFileDoesNotExist(base_path('vendor/statamic/ssg'));
        $this->assertComposerJsonDoesntHave('statamic/ssg');

        $this->installCoolRunnings();

        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileDoesNotExist(base_path('vendor/statamic/cool-runnings'));
        $this->assertFileExists(base_path('vendor/statamic/ssg'));
        $this->assertComposerJsonHasPackageVersion('require-dev', 'statamic/ssg', '*');
    }

    /** @test */
    public function it_installs_both_types_of_dependencies()
    {
        $this->setConfig([
            'export_paths' => [
                'config',
            ],
            'dependencies' => [
                'statamic/seo-pro' => '^0.2.0',
                'bobsled/speed-calculator' => '^1.0.0',
            ],
            'dependencies_dev' => [
                'statamic/ssg' => '*',
            ],
        ]);

        $this->assertFileDoesNotExist(base_path('vendor/statamic/cool-runnings'));
        $this->assertFileDoesNotExist(base_path('vendor/statamic/seo-pro'));
        $this->assertComposerJsonDoesntHave('statamic/seo-pro');
        $this->assertFileDoesNotExist(base_path('vendor/bobsled/speed-calculator'));
        $this->assertComposerJsonDoesntHave('bobsled/speed-calculator');
        $this->assertFileDoesNotExist(base_path('vendor/statamic/ssg'));
        $this->assertComposerJsonDoesntHave('statamic/ssg');

        $this->installCoolRunnings();

        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileDoesNotExist(base_path('vendor/statamic/cool-runnings'));
        $this->assertFileExists(base_path('vendor/statamic/seo-pro'));
        $this->assertComposerJsonHasPackageVersion('require', 'statamic/seo-pro', '^0.2.0');
        $this->assertFileExists(base_path('vendor/bobsled/speed-calculator'));
        $this->assertComposerJsonHasPackageVersion('require', 'bobsled/speed-calculator', '^1.0.0');
        $this->assertFileExists(base_path('vendor/statamic/ssg'));
        $this->assertComposerJsonHasPackageVersion('require-dev', 'statamic/ssg', '*');
    }

    /** @test */
    public function it_removes_dependency_versions_in_starter_kit_config_to_encourage_management_with_composer()
    {
        $this->setConfig([
            'export_paths' => [
                'config',
            ],
            'dependencies' => [
                'statamic/seo-pro' => '^0.2.0',
                'bobsled/speed-calculator' => '^1.0.0',
            ],
            'dependencies_dev' => [
                'statamic/ssg' => '*',
            ],
        ]);

        $this->installCoolRunnings(['--with-config' => true]);

        $this->assertComposerJsonHasPackageVersion('require', 'statamic/seo-pro', '^0.2.0');
        $this->assertComposerJsonHasPackageVersion('require', 'bobsled/speed-calculator', '^1.0.0');
        $this->assertComposerJsonHasPackageVersion('require-dev', 'statamic/ssg', '*');

        $expected = <<<"EOT"
export_paths:
  - config
dependencies:
  - statamic/seo-pro
  - bobsled/speed-calculator
  - statamic/ssg\n
EOT;

        $this->assertEquals($expected, $this->files->get(base_path('starter-kit.yaml')));
    }

    /** @test */
    public function it_leaves_dependency_versions_in_starter_kit_config_if_dependencies_are_not_installed()
    {
        $this->setConfig([
            'export_paths' => [
                'config',
            ],
            'dependencies' => [
                'statamic/seo-pro' => '^0.2.0',
                'bobsled/speed-calculator' => '^1.0.0',
            ],
            'dependencies_dev' => [
                'statamic/ssg' => '*',
            ],
        ]);

        $this->installCoolRunnings(['--with-config' => true, '--without-dependencies' => true]);

        $this->assertComposerJsonDoesntHave('statamic/seo-pro');
        $this->assertComposerJsonDoesntHave('bobsled/speed-calculator');
        $this->assertComposerJsonDoesntHave('statamic/ssg');

        $expected = <<<"EOT"
export_paths:
  - config
dependencies:
  statamic/seo-pro: ^0.2.0
  bobsled/speed-calculator: ^1.0.0
dependencies_dev:
  statamic/ssg: '*'\n
EOT;

        $this->assertEquals($expected, $this->files->get(base_path('starter-kit.yaml')));
    }

    /** @test */
    public function it_installs_paid_starter_kit_with_valid_license_key()
    {
        Config::set('statamic.system.license_key', 'site-key');

        $this->assertFileDoesNotExist($this->kitVendorPath());
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileDoesNotExist(base_path('copied.md'));

        $this->installCoolRunnings([], [
            'outpost.*/v3/starter-kits/statamic/cool-runnings' => Http::response(['data' => [
                'price' => 100,
                'slug' => 'cool-runnings',
                'seller' => ['slug' => 'statamic'],
            ]], 200),
            'outpost.*/v3/starter-kits/validate' => Http::response(['data' => [
                'valid' => true,
            ]], 200),
            '*' => Http::response('', 200),
        ]);

        $this->assertFalse(Blink::has('starter-kit-repository-added'));
        $this->assertFileDoesNotExist($this->kitVendorPath());
        $this->assertFileDoesNotExist(base_path('composer.json.bak'));
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileExists(base_path('copied.md'));
    }

    /** @test */
    public function it_doesnt_install_paid_starter_kit_with_invalid_license_key()
    {
        Config::set('statamic.system.license_key', 'site-key');

        $this->assertFileDoesNotExist($this->kitVendorPath());
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileDoesNotExist(base_path('copied.md'));

        $this->installCoolRunnings([], [
            'outpost.*/v3/starter-kits/statamic/cool-runnings' => Http::response(['data' => [
                'price' => 100,
                'slug' => 'cool-runnings',
                'seller' => ['slug' => 'statamic'],
            ]], 200),
            'outpost.*/v3/starter-kits/validate' => Http::response(['data' => [
                'valid' => false,
            ]], 200),
            '*' => Http::response('', 200),
        ]);

        $this->assertFalse(Blink::has('starter-kit-repository-added'));
        $this->assertFileDoesNotExist($this->kitVendorPath());
        $this->assertFileDoesNotExist(base_path('composer.json.bak'));
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileDoesNotExist(base_path('copied.md'));
    }

    /** @test */
    public function it_runs_post_install_script_hook_when_available()
    {
        $mock = Mockery::mock();
        $mock->shouldReceive('handle')
            ->withArgs(fn ($arg) => $arg instanceof InstallCommand)
            ->once();

        Hook::shouldReceive('find')
            ->with($this->kitVendorPath('StarterKitPostInstall.php'))
            ->once()
            ->andReturn($mock);

        $this->installCoolRunnings();
    }

    /** @test */
    public function it_can_register_and_run_newly_installed_command_in_post_install_hook()
    {
        Hook::shouldReceive('find')->andReturn(new StarterKitPostInstall);

        $this->assertFalse(Blink::has('starter-kit-command-run'));

        $this->installCoolRunnings();

        $this->assertTrue(Blink::has('starter-kit-command-run'));
    }

    /** @test */
    public function it_caches_post_install_hook_instructions_when_tty_is_not_available_during_a_cli_install()
    {
        $mock = Mockery::mock();
        $mock->shouldReceive('handle')->never();

        Hook::shouldReceive('find')
            ->with($this->kitVendorPath('StarterKitPostInstall.php'))
            ->once()
            ->andReturn($mock);

        TtyDetector::shouldReceive('isTtySupported')->andReturn(false);

        $this->installCoolRunnings(['--cli-install' => true]);

        $cachedInstructionsPath = storage_path('statamic/tmp/cli/post-install-instructions.txt');

        $this->assertFileExists($cachedInstructionsPath);
        $this->assertFileHasContent('Warning', $cachedInstructionsPath);
        $this->assertFileHasContent('php please starter-kit:run-post-install statamic/cool-runnings', $cachedInstructionsPath);

        // Ensure the starter kit repo is not cleaned up so that `starter-kit:run-post-install` can be run by the
        // user afterwards. It will be cleaned up after the post-install hook is successfully run instead.
        $this->assertFileExists(base_path('vendor/statamic/cool-runnings'));
    }

    /** @test */
    public function it_doesnt_caches_post_install_hook_instructions_when_not_being_run_as_a_cli_install()
    {
        $mock = Mockery::mock();
        $mock->shouldReceive('handle')
            ->withArgs(fn ($arg) => $arg instanceof InstallCommand)
            ->once();

        Hook::shouldReceive('find')
            ->with($this->kitVendorPath('StarterKitPostInstall.php'))
            ->once()
            ->andReturn($mock);

        TtyDetector::shouldReceive('isTtySupported')->andReturn(false);

        $this->installCoolRunnings(['--cli-install' => false]);

        $this->assertFileDoesNotExist(storage_path('statamic/tmp/cli/post-install-instructions.txt'));
        $this->assertFileDoesNotExist(base_path('vendor/statamic/cool-runnings'));
    }

    /** @test */
    public function it_parses_branch_from_package_param_when_installing()
    {
        $this->assertFileDoesNotExist($this->kitVendorPath());
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileDoesNotExist(base_path('copied.md'));

        $this->installCoolRunnings([
            'package' => 'statamic/cool-runnings:dev-custom-branch',
        ]);

        // Ensure `Composer::requireDev()` gets called with `package:branch`
        $this->assertEquals(Blink::get('composer-require-dev-package'), 'statamic/cool-runnings');
        $this->assertEquals(Blink::get('composer-require-dev-branch'), 'dev-custom-branch');

        // But ensure the rest of the installer handles parsed `package` without branch messing things up
        $this->assertFalse(Blink::has('starter-kit-repository-added'));
        $this->assertFileDoesNotExist($this->kitVendorPath());
        $this->assertFileDoesNotExist(base_path('composer.json.bak'));
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileExists(base_path('copied.md'));
    }

    /** @test */
    public function it_installs_branch_with_slash_without_failing_package_validation()
    {
        $this->assertFileDoesNotExist($this->kitVendorPath());
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileDoesNotExist(base_path('copied.md'));

        $this->installCoolRunnings([
            'package' => 'statamic/cool-runnings:dev-feature/custom-branch',
        ]);

        // Ensure `Composer::requireDev()` gets called with `package:branch`
        $this->assertEquals(Blink::get('composer-require-dev-package'), 'statamic/cool-runnings');
        $this->assertEquals(Blink::get('composer-require-dev-branch'), 'dev-feature/custom-branch');

        // But ensure the rest of the installer handles parsed `package` without branch messing things up
        $this->assertFalse(Blink::has('starter-kit-repository-added'));
        $this->assertFileDoesNotExist($this->kitVendorPath());
        $this->assertFileDoesNotExist(base_path('composer.json.bak'));
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileExists(base_path('copied.md'));
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
    }

    private function setConfig($config)
    {
        $this->files->put($this->kitRepoPath('starter-kit.yaml'), YAML::dump($config));
    }

    private function preparePath($path)
    {
        $folder = preg_replace('/(.*)\/[^\/]+\.[^\/]+/', '$1', $path);

        if (! $this->files->exists($folder)) {
            $this->files->makeDirectory($folder, 0755, true);
        }

        return $path;
    }

    private function installCoolRunnings($options = [], $customFake = null)
    {
        Http::fake($customFake ?? [
            'outpost.*' => Http::response(['data' => ['price' => null]], 200),
            'repo.packagist.org/*' => Http::response('', 200),
            '*' => Http::response('', 404),
        ]);

        $this->artisan('statamic:starter-kit:install', array_merge([
            'package' => 'statamic/cool-runnings',
            '--no-interaction' => true,
        ], $options));
    }

    private function assertFileHasContent($expected, $path)
    {
        $this->assertFileExists($path);

        $this->assertStringContainsString($expected, $this->files->get($path));
    }

    private function assertFileDoesntHaveContent($expected, $path)
    {
        $this->assertFileExists($path);

        $this->assertStringNotContainsString($expected, $this->files->get($path));
    }

    private function assertComposerJsonHasPackageVersion($requireKey, $package, $version)
    {
        $composerJson = json_decode($this->files->get(base_path('composer.json')), true);

        $this->assertEquals($version, $composerJson[$requireKey][$package]);
    }

    private function assertComposerJsonDoesntHave($package)
    {
        $this->assertFileDoesntHaveContent($package, base_path('composer.json'));
    }
}

class StarterKitPostInstall
{
    public $registerCommands = [
        StarterKitTestCommand::class,
    ];

    public function handle($console)
    {
        $console->call('statamic:test:starter-kit-command');
    }
}

class StarterKitTestCommand extends \Illuminate\Console\Command
{
    protected $name = 'statamic:test:starter-kit-command';

    public function handle()
    {
        Blink::put('starter-kit-command-run', true);
    }
}
