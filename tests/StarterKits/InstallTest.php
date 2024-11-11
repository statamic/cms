<?php

namespace Tests\StarterKits;

use Facades\Statamic\Console\Processes\Composer;
use Facades\Statamic\Console\Processes\TtyDetector;
use Facades\Statamic\StarterKits\Hook;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
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

        if ($this->files->exists($kitRepo = $this->kitRepoPath())) {
            $this->files->delete($kitRepo);
        }

        parent::tearDown();
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function it_fails_if_starter_kit_config_does_not_exist()
    {
        $this->files->delete($this->kitRepoPath('starter-kit.yaml'));

        $this->installCoolRunnings();

        $this->assertFileDoesNotExist(base_path('copied.md'));
    }

    #[Test]
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

    #[Test]
    public function it_merges_folders()
    {
        $this->files->put($this->preparePath(base_path('content/collections/pages/contact.md')), 'Contact');

        $this->assertFileExists(base_path('content/collections/pages/contact.md'));
        $this->assertFileDoesNotExist(base_path('content/collections/pages/home.md'));

        $this->installCoolRunnings();

        $this->assertFileExists(base_path('content/collections/pages/contact.md'));
        $this->assertFileExists(base_path('content/collections/pages/home.md'));
    }

    #[Test]
    public function it_doesnt_copy_files_not_defined_as_export_paths()
    {
        $this->assertFileDoesNotExist(base_path('copied.md'));
        $this->assertFileDoesNotExist(base_path('not-copied.md'));

        $this->installCoolRunnings();

        $this->assertFileExists(base_path('copied.md'));
        $this->assertFileDoesNotExist(base_path('not-copied.md'));
    }

    #[Test]
    public function it_overwrites_files()
    {
        $this->assertFileExists(config_path('filesystems.php'));
        $this->assertFileDoesntHaveContent('bobsled_pics', config_path('filesystems.php'));

        $this->installCoolRunnings();

        $this->assertFileHasContent('bobsled_pics', config_path('filesystems.php'));
    }

    #[Test]
    public function it_doesnt_copy_starter_kit_config_by_default()
    {
        $this->installCoolRunnings();

        $this->assertFileDoesNotExist(base_path('starter-kit.yaml'));
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function it_doesnt_clear_site_by_default()
    {
        $this->files->put($this->preparePath(base_path('content/collections/pages/contact.md')), 'Contact');
        $this->files->put($this->preparePath(base_path('content/collections/blog/article.md')), 'Article');

        $this->installCoolRunnings();

        $this->assertFileExists(base_path('content/collections/pages/home.md'));
        $this->assertFileExists(base_path('content/collections/pages/contact.md'));
        $this->assertFileExists(base_path('content/collections/blog/article.md'));
    }

    #[Test]
    public function it_clears_site_when_option_is_passed()
    {
        $this->files->put($this->preparePath(base_path('content/collections/pages/contact.md')), 'Contact');
        $this->files->put($this->preparePath(base_path('content/collections/blog/article.md')), 'Article');

        $this->installCoolRunnings(['--clear-site' => true]);

        $this->assertFileExists(base_path('content/collections/pages/home.md'));
        $this->assertFileDoesNotExist(base_path('content/collections/pages/contact.md'));
        $this->assertFileDoesNotExist(base_path('content/collections/blog'));
    }

    #[Test]
    public function it_clears_site_when_interactively_confirmed()
    {
        $this->files->put($this->preparePath(base_path('content/collections/pages/contact.md')), 'Contact');
        $this->files->put($this->preparePath(base_path('content/collections/blog/article.md')), 'Article');

        $this
            ->installCoolRunningsInteractively(['--without-user' => true])
            ->expectsConfirmation('Clear site first?', 'yes');

        $this->assertFileExists(base_path('content/collections/pages/home.md'));
        $this->assertFileDoesNotExist(base_path('content/collections/pages/contact.md'));
        $this->assertFileDoesNotExist(base_path('content/collections/blog'));
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function it_can_register_and_run_newly_installed_command_in_post_install_hook()
    {
        Hook::shouldReceive('find')->andReturn(new StarterKitPostInstall);

        $this->assertFalse(Blink::has('starter-kit-command-run'));

        $this->installCoolRunnings();

        $this->assertTrue(Blink::has('starter-kit-command-run'));
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function it_installs_no_modules_by_default_when_running_non_interactively()
    {
        $this->setConfig([
            'export_paths' => [
                'copied.md',
            ],
            'modules' => [
                'seo' => [
                    'export_paths' => [
                        'resources/css/seo.css',
                    ],
                    'dependencies' => [
                        'statamic/seo-pro' => '^0.2.0',
                    ],
                ],
                'bobsled' => [
                    'export_paths' => [
                        'resources/css/bobsled.css',
                    ],
                    'dependencies' => [
                        'bobsled/speed-calculator' => '^1.0.0',
                    ],
                ],
                'jamaica' => [
                    'export_as' => [
                        'resources/css/theme.css' => 'resources/css/jamaica.css',
                    ],
                ],
            ],
        ]);

        $this->assertFileDoesNotExist(base_path('copied.md'));
        $this->assertFileDoesNotExist(base_path('resources/css/seo.css'));
        $this->assertFileDoesNotExist(base_path('resources/css/bobsled.css'));
        $this->assertFileDoesNotExist(base_path('resources/css/theme.css'));
        $this->assertComposerJsonDoesntHave('statamic/seo-pro');
        $this->assertComposerJsonDoesntHave('bobsled/speed-calculator');

        $this->installCoolRunnings();

        $this->assertFileExists(base_path('copied.md'));
        $this->assertFileDoesNotExist(base_path('resources/css/seo.css'));
        $this->assertFileDoesNotExist(base_path('resources/css/bobsled.css'));
        $this->assertFileDoesNotExist(base_path('resources/css/theme.css'));
        $this->assertComposerJsonDoesntHave('statamic/seo-pro');
        $this->assertComposerJsonDoesntHave('bobsled/speed-calculator');
    }

    #[Test]
    public function it_installs_modules_with_prompt_false_config_by_default_when_running_non_interactively()
    {
        $this->setConfig([
            'export_paths' => [
                'copied.md',
            ],
            'modules' => [
                'seo' => [
                    'prompt' => false, // Setting prompt to false skips confirmation, so this module should still get installed non-interactively
                    'export_paths' => [
                        'resources/css/seo.css',
                    ],
                    'dependencies' => [
                        'statamic/seo-pro' => '^0.2.0',
                    ],
                ],
                'bobsled' => [
                    'export_paths' => [
                        'resources/css/bobsled.css',
                    ],
                    'dependencies' => [
                        'bobsled/speed-calculator' => '^1.0.0',
                    ],
                ],
                'jamaica' => [
                    'prompt' => false, // Setting prompt to false skips confirmation, so this module should still get installed non-interactively
                    'export_as' => [
                        'resources/css/theme.css' => 'resources/css/jamaica.css',
                    ],
                ],
            ],
        ]);

        $this->assertFileDoesNotExist(base_path('copied.md'));
        $this->assertFileDoesNotExist(base_path('resources/css/seo.css'));
        $this->assertFileDoesNotExist(base_path('resources/css/bobsled.css'));
        $this->assertFileDoesNotExist(base_path('resources/css/theme.css'));
        $this->assertComposerJsonDoesntHave('statamic/seo-pro');
        $this->assertComposerJsonDoesntHave('bobsled/speed-calculator');

        $this->installCoolRunnings();

        $this->assertFileExists(base_path('copied.md'));
        $this->assertFileExists(base_path('resources/css/seo.css'));
        $this->assertFileDoesNotExist(base_path('resources/css/bobsled.css'));
        $this->assertFileExists(base_path('resources/css/theme.css'));
        $this->assertComposerJsonHasPackageVersion('require', 'statamic/seo-pro', '^0.2.0');
        $this->assertComposerJsonDoesntHave('bobsled/speed-calculator');
    }

    #[Test]
    public function it_installs_only_the_modules_confirmed_interactively_via_prompt()
    {
        $this->setConfig([
            'export_paths' => [
                'copied.md',
            ],
            'modules' => [
                'seo' => [
                    'export_paths' => [
                        'resources/css/seo.css',
                    ],
                    'dependencies' => [
                        'statamic/seo-pro' => '^0.2.0',
                    ],
                ],
                'bobsled' => [
                    'export_paths' => [
                        'resources/css/bobsled.css',
                    ],
                    'dependencies' => [
                        'bobsled/speed-calculator' => '^1.0.0',
                    ],
                ],
                'jamaica' => [
                    'export_as' => [
                        'resources/css/theme.css' => 'resources/css/jamaica.css',
                    ],
                ],
                'js' => [
                    'options' => [
                        'react' => [
                            'export_paths' => [
                                'resources/js/react.js',
                            ],
                        ],
                        'vue' => [
                            'export_paths' => [
                                'resources/js/vue.js',
                            ],
                            'dependencies' => [
                                'bobsled/vue-components' => '^1.5',
                            ],
                        ],
                        'svelte' => [
                            'export_paths' => [
                                'resources/js/svelte.js',
                            ],
                        ],
                    ],
                ],
                'oldschool_js' => [
                    'options' => [
                        'jquery' => [
                            'export_paths' => [
                                'resources/js/jquery.js',
                            ],
                        ],
                        'mootools' => [
                            'export_paths' => [
                                'resources/js/jquery.js',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertFileDoesNotExist(base_path('copied.md'));
        $this->assertFileDoesNotExist(base_path('resources/css/seo.css'));
        $this->assertFileDoesNotExist(base_path('resources/css/bobsled.css'));
        $this->assertFileDoesNotExist(base_path('resources/css/theme.css'));
        $this->assertFileDoesNotExist(base_path('resources/js/react.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/vue.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/svelte.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/jquery.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/mootools.js'));
        $this->assertComposerJsonDoesntHave('statamic/seo-pro');
        $this->assertComposerJsonDoesntHave('bobsled/speed-calculator');
        $this->assertComposerJsonDoesntHave('bobsled/vue-components');

        $this
            ->installCoolRunningsModules()
            ->expectsConfirmation('Would you like to install the [seo] module?', 'yes')
            ->expectsConfirmation('Would you like to install the [bobsled] module?', 'no')
            ->expectsConfirmation('Would you like to install the [jamaica] module?', 'yes')
            ->expectsQuestion('Would you like to install one of the following [js] modules?', 'vue')
            ->expectsQuestion('Would you like to install one of the following [oldschool js] modules?', 'skip_module');

        $this->assertFileExists(base_path('copied.md'));
        $this->assertFileExists(base_path('resources/css/seo.css'));
        $this->assertFileDoesNotExist(base_path('resources/css/bobsled.css'));
        $this->assertFileExists(base_path('resources/css/theme.css'));
        $this->assertFileDoesNotExist(base_path('resources/js/react.js'));
        $this->assertFileExists(base_path('resources/js/vue.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/svelte.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/jquery.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/mootools.js'));
        $this->assertComposerJsonHasPackageVersion('require', 'statamic/seo-pro', '^0.2.0');
        $this->assertComposerJsonDoesntHave('bobsled/speed-calculator');
        $this->assertComposerJsonHasPackageVersion('require', 'bobsled/vue-components', '^1.5');
    }

    #[Test]
    public function it_installs_imported_modules_confirmed_interactively_via_prompt()
    {
        $this->setConfig([
            'export_paths' => [
                'copied.md',
            ],
            'modules' => [
                'seo' => '@import', // import!
                'bobsled' => [
                    'export_paths' => [
                        'resources/css/bobsled.css',
                    ],
                    'dependencies' => [
                        'bobsled/speed-calculator' => '^1.0.0',
                    ],
                ],
                'jamaica' => [
                    'export_as' => [
                        'resources/css/theme.css' => 'resources/css/jamaica.css',
                    ],
                ],
                'js' => '@import', // import!
                'oldschool_js' => [
                    'options' => [
                        'jquery' => [
                            'export_paths' => [
                                'resources/js/jquery.js',
                            ],
                        ],
                        'mootools' => [
                            'export_paths' => [
                                'resources/js/jquery.js',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->setConfig(
            path: 'modules/seo/module.yaml',
            config: [
                'export_paths' => [
                    $this->moveKitRepoFile('modules/seo', 'resources/css/seo.css'),
                ],
                'dependencies' => [
                    'statamic/seo-pro' => '^0.2.0',
                ],
            ],
        );

        $this->setConfig(
            path: 'modules/js/module.yaml',
            config: [
                'options' => [
                    'react' => [
                        'export_paths' => [
                            $this->moveKitRepoFile('modules/js', 'resources/js/react.js'),
                        ],
                    ],
                    'vue' => '@import', // import option as separate module!
                    'svelte' => [
                        'export_paths' => [
                            $this->moveKitRepoFile('modules/js', 'resources/js/svelte.js'),
                        ],
                    ],
                ],
            ],
        );

        $this->setConfig(
            path: 'modules/js/vue/module.yaml',
            config: [
                'export_paths' => [
                    $this->moveKitRepoFile('modules/js/vue', 'resources/js/vue.js'),
                ],
                'dependencies' => [
                    'bobsled/vue-components' => '^1.5',
                ],
            ],
        );

        $this->assertFileDoesNotExist(base_path('copied.md'));
        $this->assertFileDoesNotExist(base_path('resources/css/seo.css'));
        $this->assertFileDoesNotExist(base_path('resources/css/bobsled.css'));
        $this->assertFileDoesNotExist(base_path('resources/css/theme.css'));
        $this->assertFileDoesNotExist(base_path('resources/js/react.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/vue.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/svelte.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/jquery.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/mootools.js'));
        $this->assertComposerJsonDoesntHave('statamic/seo-pro');
        $this->assertComposerJsonDoesntHave('bobsled/speed-calculator');
        $this->assertComposerJsonDoesntHave('bobsled/vue-components');

        $this
            ->installCoolRunningsModules()
            ->expectsConfirmation('Would you like to install the [seo] module?', 'yes')
            ->expectsConfirmation('Would you like to install the [bobsled] module?', 'no')
            ->expectsConfirmation('Would you like to install the [jamaica] module?', 'yes')
            ->expectsQuestion('Would you like to install one of the following [js] modules?', 'vue')
            ->expectsQuestion('Would you like to install one of the following [oldschool js] modules?', 'skip_module');

        $this->assertFileExists(base_path('copied.md'));
        $this->assertFileExists(base_path('resources/css/seo.css'));
        $this->assertFileDoesNotExist(base_path('resources/css/bobsled.css'));
        $this->assertFileExists(base_path('resources/css/theme.css'));
        $this->assertFileDoesNotExist(base_path('resources/js/react.js'));
        $this->assertFileExists(base_path('resources/js/vue.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/svelte.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/jquery.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/mootools.js'));
        $this->assertComposerJsonHasPackageVersion('require', 'statamic/seo-pro', '^0.2.0');
        $this->assertComposerJsonDoesntHave('bobsled/speed-calculator');
        $this->assertComposerJsonHasPackageVersion('require', 'bobsled/vue-components', '^1.5');
    }

    #[Test]
    public function it_displays_custom_module_prompts()
    {
        $this->setConfig([
            'modules' => [
                'seo' => [
                    'prompt' => 'Want some extra SEO magic?',
                    'dependencies' => [
                        'statamic/seo-pro' => '^0.2.0',
                    ],
                ],
                'js' => [
                    'prompt' => 'Want one of these fancy JS options?',
                    'options' => [
                        'react' => [
                            'label' => 'React JS',
                            'export_paths' => [
                                'resources/js/react.js',
                            ],
                        ],
                        'vue' => [
                            'label' => 'Vue JS',
                            'export_paths' => [
                                'resources/js/vue.js',
                            ],
                        ],
                        'svelte' => [
                            'export_paths' => [
                                'resources/js/svelte.js',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertComposerJsonDoesntHave('statamic/seo-pro');
        $this->assertFileDoesNotExist(base_path('resources/js/react.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/vue.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/svelte.js'));

        $command = $this
            ->installCoolRunningsModules()
            ->expectsConfirmation('Want some extra SEO magic?', 'yes');

        // Some fixes to `expectsChoice()` were merged for us, but are not available on 11.20.0 and below
        // See: https://github.com/laravel/framework/pull/52408
        if (version_compare(app()->version(), '11.20.0', '>')) {
            $command->expectsChoice('Want one of these fancy JS options?', 'svelte', [
                'skip_module' => 'No',
                'react' => 'React JS',
                'vue' => 'Vue JS',
                'svelte' => 'Svelte',
            ]);
        } else {
            $command->expectsQuestion('Want one of these fancy JS options?', 'svelte');
        }

        $command->run();

        $this->assertComposerJsonHasPackageVersion('require', 'statamic/seo-pro', '^0.2.0');
        $this->assertFileDoesNotExist(base_path('resources/js/react.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/vue.js'));
        $this->assertFileExists(base_path('resources/js/svelte.js'));
    }

    #[Test]
    public function it_can_merge_imported_module_config_with_starter_kit_config()
    {
        $this->setConfig([
            'modules' => [
                'seo' => [
                    'prompt' => 'Want some extra SEO magic?', // handle prompt flow here
                    'import' => '@config', // but import and merge rest of config
                ],
                'js' => [
                    'prompt' => 'Want one of these fancy JS options?',
                    'options' => [
                        'react' => [
                            'label' => 'React JS', // handle prompt option label here
                            'import' => '@config', // but import and merge rest of config
                        ],
                        'svelte' => [
                            'export_paths' => [
                                'resources/js/svelte.js',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->setConfig(
            path: 'modules/seo/module.yaml',
            config: [
                'prompt' => 'This should not get used, because prompt config in starter-kit.yaml takes precedence!',
                'dependencies' => [
                    'statamic/seo-pro' => '^0.2.0', // but this should still be imported and installed
                ],
            ],
        );

        $this->setConfig(
            path: 'modules/js/react/module.yaml',
            config: [
                'label' => 'This should not get used, because prompt config in starter-kit.yaml takes precedence!',
                'export_paths' => [
                    $this->moveKitRepoFile('modules/js/react', 'resources/js/react.js'),
                ],
            ],
        );

        $this->assertComposerJsonDoesntHave('statamic/seo-pro');
        $this->assertFileDoesNotExist(base_path('resources/js/react.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/vue.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/svelte.js'));

        $command = $this
            ->installCoolRunningsModules()
            ->expectsConfirmation('Want some extra SEO magic?', 'yes');

        // Some fixes to `expectsChoice()` were merged for us, but are not available on 11.20.0 and below
        // See: https://github.com/laravel/framework/pull/52408
        if (version_compare(app()->version(), '11.20.0', '>')) {
            $command->expectsChoice('Want one of these fancy JS options?', 'react', [
                'skip_module' => 'No',
                'react' => 'React JS',
                'svelte' => 'Svelte',
            ]);
        } else {
            $command->expectsQuestion('Want one of these fancy JS options?', 'react');
        }

        $command->run();

        $this->assertComposerJsonHasPackageVersion('require', 'statamic/seo-pro', '^0.2.0');
        $this->assertFileExists(base_path('resources/js/react.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/svelte.js'));
    }

    #[Test]
    public function it_installs_modules_without_dependencies()
    {
        $this->setConfig([
            'export_paths' => [
                'copied.md',
            ],
            'dependencies' => [
                'bobsled/speed-calculator' => '^1.0.0',
            ],
            'modules' => [
                'seo' => [
                    'export_paths' => [
                        'resources/css/seo.css',
                    ],
                    'dependencies' => [
                        'statamic/seo-pro' => '^0.2.0',
                    ],
                ],
            ],
        ]);

        $this->assertFileDoesNotExist(base_path('copied.md'));
        $this->assertFileDoesNotExist(base_path('resources/css/seo.css'));
        $this->assertComposerJsonDoesntHave('statamic/seo-pro');
        $this->assertComposerJsonDoesntHave('bobsled/speed-calculator');

        $this
            ->installCoolRunningsModules(['--without-dependencies' => true])
            ->expectsConfirmation('Would you like to install the [seo] module?', 'yes');

        $this->assertFileExists(base_path('copied.md'));
        $this->assertFileExists(base_path('resources/css/seo.css'));
        $this->assertComposerJsonDoesntHave('statamic/seo-pro');
        $this->assertComposerJsonDoesntHave('bobsled/speed-calculator');
    }

    #[Test]
    public function it_requires_valid_config_at_top_level()
    {
        $this->setConfig([
            // no installable config!
        ]);

        $this->assertFileDoesNotExist(base_path('copied.md'));

        $this
            ->installCoolRunnings()
            ->expectsOutput('Starter-kit module is missing `export_paths`, `dependencies`, or nested `modules`!')
            ->assertFailed();

        $this->assertFileDoesNotExist(base_path('copied.md'));
    }

    #[Test]
    public function it_requires_valid_module_config()
    {
        $this->setConfig([
            'modules' => [
                'seo' => [
                    'prompt' => false,
                    // no installable config!
                ],
            ],
        ]);

        $this->assertFileDoesNotExist(base_path('copied.md'));

        $this
            ->installCoolRunnings()
            ->expectsOutput('Starter-kit module is missing `export_paths`, `dependencies`, or nested `modules`!')
            ->assertFailed();

        $this->assertFileDoesNotExist(base_path('copied.md'));
    }

    #[Test]
    public function it_doesnt_require_anything_installable_if_module_contains_nested_modules()
    {
        $this->setConfig([
            'modules' => [
                'seo' => [
                    'prompt' => false,
                    'modules' => [
                        'js' => [
                            'prompt' => false,
                            'export_paths' => [
                                'copied.md',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertFileDoesNotExist(base_path('copied.md'));

        $this
            ->installCoolRunnings()
            ->assertSuccessful();

        $this->assertFileExists(base_path('copied.md'));
    }

    #[Test]
    public function it_requires_imported_module_folder_config()
    {
        $this->setConfig([
            'modules' => [
                'seo' => '@import',
            ],
        ]);

        $this->assertFileDoesNotExist(base_path('copied.md'));

        $this
            ->installCoolRunnings()
            ->expectsOutput('Starter kit module config [modules/seo/module.yaml] does not exist.')
            ->assertFailed();

        $this->assertFileDoesNotExist(base_path('copied.md'));
    }

    #[Test]
    public function it_requires_nested_imported_module_folder_config()
    {
        $this->setConfig([
            'modules' => [
                'seo' => '@import',
            ],
        ]);

        $this->setConfig(
            path: 'modules/seo/module.yaml',
            config: [
                'modules' => [
                    'js' => [
                        'options' => [
                            'vue' => '@import',
                        ],
                    ],
                ],
            ],
        );

        $this->assertFileDoesNotExist(base_path('copied.md'));

        $this
            ->installCoolRunnings()
            ->expectsOutput('Starter kit module config [modules/seo/js/vue/module.yaml] does not exist.')
            ->assertFailed();

        $this->assertFileDoesNotExist(base_path('copied.md'));
    }

    #[Test]
    public function it_requires_valid_imported_module_folder_config()
    {
        $this->setConfig([
            'modules' => [
                'seo' => '@import',
            ],
        ]);

        $this->setConfig(
            path: 'modules/seo/module.yaml',
            config: [
                'prompt' => false,
                // no installable config!
            ]
        );

        $this->assertFileDoesNotExist(base_path('copied.md'));

        $this
            ->installCoolRunnings()
            ->expectsOutput('Starter-kit module is missing `export_paths`, `dependencies`, or nested `modules`!')
            ->assertFailed();

        $this->assertFileDoesNotExist(base_path('copied.md'));
    }

    #[Test]
    #[DataProvider('validModuleConfigs')]
    public function it_passes_validation_if_module_export_paths_or_dependencies_or_nested_modules_are_properly_configured($config)
    {
        $this->setConfig([
            'modules' => [
                'seo' => array_merge(['prompt' => false], $config),
            ],
        ]);

        $this
            ->installCoolRunnings()
            ->assertSuccessful();
    }

    public static function validModuleConfigs()
    {
        return [
            'export paths' => [[
                'export_paths' => [
                    'copied.md',
                ],
            ]],
            'export as paths' => [[
                'export_as' => [
                    'copied.md' => 'resources/js/vue.js',
                ],
            ]],
            'dependencies' => [[
                'dependencies' => [
                    'statamic/seo-pro' => '^1.0',
                ],
            ]],
            'dev dependencies' => [[
                'dependencies_dev' => [
                    'statamic/seo-pro' => '^1.0',
                ],
            ]],
            'nested modules' => [[
                'modules' => [
                    'js' => [
                        'export_paths' => [
                            'resources/js/vue.js',
                        ],
                    ],
                ],
            ]],
        ];
    }

    #[Test]
    public function it_installs_nested_modules_with_prompt_false_config_by_default_when_running_non_interactively()
    {
        $this->setConfig([
            'export_paths' => [
                'copied.md',
            ],
            'modules' => [
                'canada' => [
                    'prompt' => false, // Setting prompt to false skips confirmation, so this module should still get installed non-interactively
                    'export_paths' => [
                        'resources/css/hockey.css',
                    ],
                    'modules' => [
                        'hockey_players' => [
                            'prompt' => false, // Setting prompt to false skips confirmation, so this module should still get installed non-interactively
                            'export_paths' => [
                                'resources/dictionaries/players.yaml',
                            ],
                            'dependencies' => [
                                'nhl/hockey-league' => '*',
                            ],
                            'modules' => [
                                'hockey_night_in_usa' => [
                                    'export_paths' => [
                                        'resources/dictionaries/american_players.yaml',
                                    ],
                                ],
                                'hockey_night_in_canada' => [
                                    'prompt' => false, // Setting prompt to false skips confirmation, so this module should still get installed non-interactively
                                    'export_paths' => [
                                        'resources/dictionaries/canadian_players.yaml',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertFileDoesNotExist(base_path('copied.md'));
        $this->assertFileDoesNotExist(base_path('resources/css/hockey.css'));
        $this->assertComposerJsonDoesntHave('nhl/hockey-league');
        $this->assertFileDoesNotExist(base_path('resources/dictionaries/players.yaml'));
        $this->assertFileDoesNotExist(base_path('resources/dictionaries/american_players.yaml'));
        $this->assertFileDoesNotExist(base_path('resources/dictionaries/canadian_players.yaml'));

        $this->installCoolRunnings();

        $this->assertFileExists(base_path('copied.md'));
        $this->assertFileExists(base_path('resources/css/hockey.css'));
        $this->assertComposerJsonHasPackageVersion('require', 'nhl/hockey-league', '*');
        $this->assertFileExists(base_path('resources/dictionaries/players.yaml'));
        $this->assertFileDoesNotExist(base_path('resources/dictionaries/american_players.yaml'));
        $this->assertFileExists(base_path('resources/dictionaries/canadian_players.yaml'));
    }

    #[Test]
    public function it_installs_nested_modules_confirmed_interactively_via_prompt()
    {
        $this->setConfig([
            'export_paths' => [
                'copied.md',
            ],
            'modules' => [
                'seo' => [
                    'export_paths' => [
                        'resources/css/seo.css',
                    ],
                    'dependencies' => [
                        'statamic/seo-pro' => '^0.2.0',
                    ],
                    'modules' => [
                        'js' => [
                            'options' => [
                                'react' => [
                                    'export_paths' => [
                                        'resources/js/react.js',
                                    ],
                                    'modules' => [
                                        'testing_tools' => [
                                            'export_paths' => [
                                                'resources/js/react-testing-tools.js',
                                            ],
                                        ],
                                    ],
                                ],
                                'vue' => [
                                    'export_paths' => [
                                        'resources/js/vue.js',
                                    ],
                                    'dependencies_dev' => [
                                        'i-love-vue/test-helpers' => '^1.5',
                                    ],
                                    'modules' => [
                                        'testing_tools' => [
                                            'export_paths' => [
                                                'resources/js/vue-testing-tools.js',
                                            ],
                                        ],
                                    ],
                                ],
                                'svelte' => [
                                    'export_paths' => [
                                        'resources/js/svelte.js',
                                    ],
                                ],
                            ],
                        ],
                        'oldschool_js' => [
                            'options' => [
                                'jquery' => [
                                    'export_paths' => [
                                        'resources/js/jquery.js',
                                    ],
                                ],
                                'mootools' => [
                                    'export_paths' => [
                                        'resources/js/jquery.js',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'canada' => [
                    'export_paths' => [
                        'resources/css/hockey.css',
                    ],
                    'modules' => [
                        'hockey_players' => [
                            'export_paths' => [
                                'resources/dictionaries/players.yaml',
                            ],
                        ],
                    ],
                ],
                'jamaica' => [
                    'export_as' => [
                        'resources/css/theme.css' => 'resources/css/jamaica.css',
                    ],
                    'modules' => [
                        'bobsled' => [
                            'export_paths' => [
                                'resources/css/bobsled.css',
                            ],
                            'dependencies' => [
                                'bobsled/speed-calculator' => '^1.0.0',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertFileDoesNotExist(base_path('copied.md'));
        $this->assertFileDoesNotExist(base_path('resources/css/seo.css'));
        $this->assertComposerJsonDoesntHave('statamic/seo-pro');
        $this->assertFileDoesNotExist(base_path('resources/js/react.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/react-testing-tools.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/vue.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/vue-testing-tools.js'));
        $this->assertComposerJsonDoesntHave('i-love-vue/test-helpers');
        $this->assertFileDoesNotExist(base_path('resources/js/svelte.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/jquery.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/mootools.js'));
        $this->assertFileDoesNotExist(base_path('resources/css/hockey.css'));
        $this->assertFileDoesNotExist(base_path('resources/dictionaries/players.yaml'));
        $this->assertFileDoesNotExist(base_path('resources/css/theme.css'));
        $this->assertFileDoesNotExist(base_path('resources/css/bobsled.css'));
        $this->assertComposerJsonDoesntHave('bobsled/speed-calculator');

        $this
            ->installCoolRunningsModules()
            ->expectsConfirmation('Would you like to install the [seo] module?', 'yes')
            ->expectsQuestion('Would you like to install one of the following [seo js] modules?', 'vue')
            ->expectsQuestion('Would you like to install the [seo js vue testing tools] module?', 'yes')
            ->expectsQuestion('Would you like to install one of the following [seo oldschool js] modules?', 'skip_module')
            ->expectsConfirmation('Would you like to install the [canada] module?', 'no')
            ->expectsConfirmation('Would you like to install the [jamaica] module?', 'yes')
            ->expectsConfirmation('Would you like to install the [jamaica bobsled] module?', 'yes');

        $this->assertFileExists(base_path('copied.md'));
        $this->assertFileExists(base_path('resources/css/seo.css'));
        $this->assertComposerJsonHasPackageVersion('require', 'statamic/seo-pro', '^0.2.0');
        $this->assertFileDoesNotExist(base_path('resources/js/react.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/react-testing-tools.js'));
        $this->assertFileExists(base_path('resources/js/vue.js'));
        $this->assertFileExists(base_path('resources/js/vue-testing-tools.js'));
        $this->assertComposerJsonHasPackageVersion('require-dev', 'i-love-vue/test-helpers', '^1.5');
        $this->assertFileDoesNotExist(base_path('resources/js/svelte.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/jquery.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/mootools.js'));
        $this->assertFileDoesNotExist(base_path('resources/css/hockey.css'));
        $this->assertFileDoesNotExist(base_path('resources/dictionaries/players.yaml'));
        $this->assertFileExists(base_path('resources/css/theme.css'));
        $this->assertFileExists(base_path('resources/css/bobsled.css'));
        $this->assertComposerJsonHasPackageVersion('require', 'bobsled/speed-calculator', '^1.0.0');
    }

    #[Test]
    public function it_installs_nested_imported_modules_confirmed_interactively_via_prompt()
    {
        $this->setConfig([
            'export_paths' => [
                'copied.md',
            ],
            'modules' => [
                'seo' => '@import',
                'canada' => [
                    'export_paths' => [
                        'resources/css/hockey.css',
                    ],
                    'modules' => [
                        'hockey_players' => [
                            'export_paths' => [
                                'resources/dictionaries/players.yaml',
                            ],
                        ],
                    ],
                ],
                'jamaica' => [
                    'export_as' => [
                        'resources/css/theme.css' => 'resources/css/jamaica.css',
                    ],
                    'modules' => [
                        'bobsled' => '@import', // import nested module!
                    ],
                ],
            ],
        ]);

        $this->setConfig(
            path: 'modules/seo/module.yaml',
            config: [
                'export_paths' => [
                    $this->moveKitRepoFile('modules/seo', 'resources/css/seo.css'),
                ],
                'dependencies' => [
                    'statamic/seo-pro' => '^0.2.0',
                ],
                'modules' => [
                    'js' => [
                        'options' => [
                            'react' => [
                                'export_paths' => [
                                    $this->moveKitRepoFile('modules/seo', 'resources/js/react.js'),
                                ],
                                'modules' => [
                                    'testing_tools' => [
                                        'export_paths' => [
                                            $this->moveKitRepoFile('modules/seo', 'resources/js/react-testing-tools.js'),
                                        ],
                                    ],
                                ],
                            ],
                            'vue' => [
                                'export_paths' => [
                                    $this->moveKitRepoFile('modules/seo', 'resources/js/vue.js'),
                                ],
                                'dependencies_dev' => [
                                    'i-love-vue/test-helpers' => '^1.5',
                                ],
                                'modules' => [
                                    'testing_tools' => '@import', // import nested module!
                                ],
                            ],
                            'svelte' => [
                                'export_paths' => [
                                    $this->moveKitRepoFile('modules/seo', 'resources/js/svelte.js'),
                                ],
                            ],
                        ],
                    ],
                    'oldschool_js' => [
                        'options' => [
                            'jquery' => [
                                'export_paths' => [
                                    $this->moveKitRepoFile('modules/seo', 'resources/js/jquery.js'),
                                ],
                            ],
                            'mootools' => [
                                'export_paths' => [
                                    $this->moveKitRepoFile('modules/seo', 'resources/js/mootools.js'),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        );

        $this->setConfig(
            path: 'modules/seo/js/vue/testing_tools/module.yaml',
            config: [
                'export_paths' => [
                    $this->moveKitRepoFile('modules/seo/js/vue/testing_tools', 'resources/js/vue-testing-tools.js'),
                ],
            ],
        );

        $this->setConfig(
            path: 'modules/jamaica/bobsled/module.yaml',
            config: [
                'export_paths' => [
                    $this->moveKitRepoFile('modules/jamaica/bobsled', 'resources/css/bobsled.css'),
                ],
                'dependencies' => [
                    'bobsled/speed-calculator' => '^1.0.0',
                ],
            ],
        );

        $this->assertFileDoesNotExist(base_path('copied.md'));
        $this->assertFileDoesNotExist(base_path('resources/css/seo.css'));
        $this->assertComposerJsonDoesntHave('statamic/seo-pro');
        $this->assertFileDoesNotExist(base_path('resources/js/react.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/react-testing-tools.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/vue.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/vue-testing-tools.js'));
        $this->assertComposerJsonDoesntHave('i-love-vue/test-helpers');
        $this->assertFileDoesNotExist(base_path('resources/js/svelte.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/jquery.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/mootools.js'));
        $this->assertFileDoesNotExist(base_path('resources/css/hockey.css'));
        $this->assertFileDoesNotExist(base_path('resources/dictionaries/players.yaml'));
        $this->assertFileDoesNotExist(base_path('resources/css/theme.css'));
        $this->assertFileDoesNotExist(base_path('resources/css/bobsled.css'));
        $this->assertComposerJsonDoesntHave('bobsled/speed-calculator');

        $this
            ->installCoolRunningsModules()
            ->expectsConfirmation('Would you like to install the [seo] module?', 'yes')
            ->expectsQuestion('Would you like to install one of the following [seo js] modules?', 'vue')
            ->expectsQuestion('Would you like to install the [seo js vue testing tools] module?', 'yes')
            ->expectsQuestion('Would you like to install one of the following [seo oldschool js] modules?', 'skip_module')
            ->expectsConfirmation('Would you like to install the [canada] module?', 'no')
            ->expectsConfirmation('Would you like to install the [jamaica] module?', 'yes')
            ->expectsConfirmation('Would you like to install the [jamaica bobsled] module?', 'yes');

        $this->assertFileExists(base_path('copied.md'));
        $this->assertFileExists(base_path('resources/css/seo.css'));
        $this->assertComposerJsonHasPackageVersion('require', 'statamic/seo-pro', '^0.2.0');
        $this->assertFileDoesNotExist(base_path('resources/js/react.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/react-testing-tools.js'));
        $this->assertFileExists(base_path('resources/js/vue.js'));
        $this->assertFileExists(base_path('resources/js/vue-testing-tools.js'));
        $this->assertComposerJsonHasPackageVersion('require-dev', 'i-love-vue/test-helpers', '^1.5');
        $this->assertFileDoesNotExist(base_path('resources/js/svelte.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/jquery.js'));
        $this->assertFileDoesNotExist(base_path('resources/js/mootools.js'));
        $this->assertFileDoesNotExist(base_path('resources/css/hockey.css'));
        $this->assertFileDoesNotExist(base_path('resources/dictionaries/players.yaml'));
        $this->assertFileExists(base_path('resources/css/theme.css'));
        $this->assertFileExists(base_path('resources/css/bobsled.css'));
        $this->assertComposerJsonHasPackageVersion('require', 'bobsled/speed-calculator', '^1.0.0');
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

    private function setConfig($config, $path = 'starter-kit.yaml')
    {
        $this->files->put($this->preparePath($this->kitRepoPath($path)), YAML::dump($config));
    }

    private function moveKitRepoFile($relativeModulePath, $relativeFilePath)
    {
        $this->files->move(
            $this->kitRepoPath($relativeFilePath),
            $this->preparePath($this->kitRepoPath(Str::ensureRight($relativeModulePath, '/').$relativeFilePath)),
        );

        return $relativeFilePath;
    }

    private function preparePath($path)
    {
        $folder = preg_replace('/(.*)\/[^\/]+\.[^\/]+/', '$1', $path);

        if (! $this->files->exists($folder)) {
            $this->files->makeDirectory($folder, 0755, true);
        }

        return $path;
    }

    private function installCoolRunnings($options = [], $customHttpFake = null)
    {
        $this->httpFake($customHttpFake);

        return $this->artisan('statamic:starter-kit:install', array_merge([
            'package' => 'statamic/cool-runnings',
            '--no-interaction' => true,
        ], $options));
    }

    private function installCoolRunningsInteractively($options = [], $customHttpFake = null)
    {
        $this->httpFake($customHttpFake);

        return $this->artisan('statamic:starter-kit:install', array_merge([
            'package' => 'statamic/cool-runnings',
        ], $options));
    }

    private function installCoolRunningsModules($options = [], $customHttpFake = null)
    {
        return $this->installCoolRunningsInteractively(array_merge($options, [
            '--clear-site' => true,   // skip clear site prompt
            '--without-user' => true, // skip create user prompt
        ]), $customHttpFake);
    }

    private function httpFake($customFake = null)
    {
        Http::fake($customFake ?? [
            'outpost.*' => Http::response(['data' => ['price' => null]], 200),
            'repo.packagist.org/*' => Http::response('', 200),
            '*' => Http::response('', 404),
        ]);
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
