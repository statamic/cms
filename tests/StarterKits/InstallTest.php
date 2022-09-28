<?php

namespace Tests\StarterKits;

use Facades\Statamic\Console\Processes\Composer;
use Facades\Statamic\StarterKits\Hook;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;
use Mockery;
use Statamic\Console\Commands\StarterKitInstall as InstallCommand;
use Statamic\Facades\Blink;
use Statamic\Facades\Config;
use Statamic\Facades\YAML;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Tests\TestCase;

class InstallTest extends TestCase
{
    use Concerns\BacksUpSite;

    protected $files;

    public function setUp(): void
    {
        parent::setUp();

        if (version_compare(app()->version(), '7', '<')) {
            $this->markTestSkipped();
        }

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
        $this->assertFileNotExists($this->kitVendorPath());
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileNotExists(base_path('copied.md'));

        $this->installCoolRunnings();

        $this->assertFalse(Blink::has('starter-kit-repository-added'));
        $this->assertFileNotExists($this->kitVendorPath());
        $this->assertFileNotExists(base_path('composer.json.bak'));
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

        $this->assertFileNotExists($this->kitVendorPath());
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileNotExists(base_path('copied.md'));
        $this->assertFileNotExists($renamedFile = base_path('README.md'));
        $this->assertFileNotExists($renamedFolder = base_path('original-dir'));

        $this->installCoolRunnings();

        $this->assertFalse(Blink::has('starter-kit-repository-added'));
        $this->assertFileNotExists($this->kitVendorPath());
        $this->assertFileNotExists(base_path('composer.json.bak'));
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileExists(base_path('copied.md'));
        $this->assertFileExists($renamedFile);
        $this->assertFileExists($renamedFolder);

        $this->assertFileNotExists(base_path('README-for-new-site.md')); // This was renamed back to original path on install
        $this->assertFileNotExists(base_path('renamed-dir')); // This was renamed back to original path on install

        $this->assertFileHasContent('This readme should get installed to README.md.', $renamedFile);
        $this->assertFileHasContent('One.', $renamedFolder.'/one.txt');
        $this->assertFileHasContent('Two.', $renamedFolder.'/two.txt');
    }

    /** @test */
    public function it_installs_from_github()
    {
        $this->assertFileNotExists($this->kitVendorPath());
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileNotExists(base_path('copied.md'));

        $this->installCoolRunnings([], [
            'outpost.*' => Http::response(['data' => ['price' => null]], 200),
            'github.com/*' => Http::response('', 200),
            '*' => Http::response('', 404),
        ]);

        $this->assertEquals('https://github.com/statamic/cool-runnings', Blink::get('starter-kit-repository-added'));
        $this->assertFileNotExists($this->kitVendorPath());
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileExists(base_path('copied.md'));
    }

    /** @test */
    public function it_installs_from_bitbucket()
    {
        $this->assertFileNotExists($this->kitVendorPath());
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileNotExists(base_path('copied.md'));

        $this->installCoolRunnings([], [
            'outpost.*' => Http::response(['data' => ['price' => null]], 200),
            'bitbucket.org/*' => Http::response('', 200),
            '*' => Http::response('', 404),
        ]);

        $this->assertEquals('https://bitbucket.org/statamic/cool-runnings.git', Blink::get('starter-kit-repository-added'));
        $this->assertFileNotExists($this->kitVendorPath());
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileExists(base_path('copied.md'));
    }

    /** @test */
    public function it_installs_from_gitlab()
    {
        $this->assertFileNotExists($this->kitVendorPath());
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileNotExists(base_path('copied.md'));

        $this->installCoolRunnings([], [
            'outpost.*' => Http::response(['data' => ['price' => null]], 200),
            'gitlab.com/*' => Http::response('', 200),
            '*' => Http::response('', 404),
        ]);

        $this->assertEquals('https://gitlab.com/statamic/cool-runnings', Blink::get('starter-kit-repository-added'));
        $this->assertFileNotExists($this->kitVendorPath());
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
        $this->assertFileNotExists($this->kitVendorPath());
        $this->assertFileNotExists(base_path('copied.md'));

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
        $this->assertFileNotExists($this->kitVendorPath());
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

        $this->assertFileNotExists(base_path('copied.md'));
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

        $this->assertFileNotExists(base_path('copied.md'));
    }

    /** @test */
    public function it_merges_folders()
    {
        $this->files->put($this->preparePath(base_path('content/collections/pages/contact.md')), 'Contact');

        $this->assertFileExists(base_path('content/collections/pages/contact.md'));
        $this->assertFileNotExists(base_path('content/collections/pages/home.md'));

        $this->installCoolRunnings();

        $this->assertFileExists(base_path('content/collections/pages/contact.md'));
        $this->assertFileExists(base_path('content/collections/pages/home.md'));
    }

    /** @test */
    public function it_doesnt_copy_files_not_defined_as_export_paths()
    {
        $this->assertFileNotExists(base_path('copied.md'));
        $this->assertFileNotExists(base_path('not-copied.md'));

        $this->installCoolRunnings();

        $this->assertFileExists(base_path('copied.md'));
        $this->assertFileNotExists(base_path('not-copied.md'));
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

        $this->assertFileNotExists(base_path('starter-kit.yaml'));
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

        $this->installCoolRunnings(['--with-config' => true]);

        $this->assertFileExists($hookPath = base_path('StarterKitPostInstall.php'));
        $this->assertFileHasContent('<?php', $hookPath);
    }

    /** @test */
    public function it_doesnt_copy_starter_kit_post_install_script_hook_when_with_config_option_is_not_passed()
    {
        $this->files->put($this->kitRepoPath('StarterKitPostInstall.php'), '<?php');

        $this->installCoolRunnings();

        $this->assertFileNotExists(base_path('StarterKitPostInstall.php'));
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
        $this->assertFileNotExists(base_path('content/collections/pages/contact.md'));
        $this->assertFileNotExists(base_path('content/collections/blog'));
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

        $this->assertFileNotExists(base_path('vendor/statamic/cool-runnings'));
        $this->assertFileNotExists(base_path('vendor/statamic/seo-pro'));
        $this->assertComposerJsonDoesntHave('statamic/seo-pro');
        $this->assertFileNotExists(base_path('vendor/bobsled/speed-calculator'));
        $this->assertComposerJsonDoesntHave('bobsled/speed-calculator');

        $this->installCoolRunnings();

        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileNotExists(base_path('vendor/statamic/cool-runnings'));
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

        $this->assertFileNotExists(base_path('vendor/statamic/cool-runnings'));
        $this->assertFileNotExists(base_path('vendor/statamic/ssg'));
        $this->assertComposerJsonDoesntHave('statamic/ssg');

        $this->installCoolRunnings();

        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileNotExists(base_path('vendor/statamic/cool-runnings'));
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

        $this->assertFileNotExists(base_path('vendor/statamic/cool-runnings'));
        $this->assertFileNotExists(base_path('vendor/statamic/seo-pro'));
        $this->assertComposerJsonDoesntHave('statamic/seo-pro');
        $this->assertFileNotExists(base_path('vendor/bobsled/speed-calculator'));
        $this->assertComposerJsonDoesntHave('bobsled/speed-calculator');
        $this->assertFileNotExists(base_path('vendor/statamic/ssg'));
        $this->assertComposerJsonDoesntHave('statamic/ssg');

        $this->installCoolRunnings();

        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileNotExists(base_path('vendor/statamic/cool-runnings'));
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

        $this->assertFileNotExists($this->kitVendorPath());
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileNotExists(base_path('copied.md'));

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
        $this->assertFileNotExists($this->kitVendorPath());
        $this->assertFileNotExists(base_path('composer.json.bak'));
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileExists(base_path('copied.md'));
    }

    /** @test */
    public function it_doesnt_install_paid_starter_kit_with_invalid_license_key()
    {
        Config::set('statamic.system.license_key', 'site-key');

        $this->assertFileNotExists($this->kitVendorPath());
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileNotExists(base_path('copied.md'));

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
        $this->assertFileNotExists($this->kitVendorPath());
        $this->assertFileNotExists(base_path('composer.json.bak'));
        $this->assertComposerJsonDoesntHave('repositories');
        $this->assertFileNotExists(base_path('copied.md'));
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

class FakeComposer
{
    public function __construct()
    {
        $this->files = app(Filesystem::class);
    }

    public function require($package, $version = null, ...$extraParams)
    {
        if (collect($extraParams)->contains('--dry-run')) {
            return;
        }

        $this->fakeInstallComposerJson('require', $package, $version);
        $this->fakeInstallVendorFiles($package);
    }

    public function requireDev($package, $version = null, ...$extraParams)
    {
        if (collect($extraParams)->contains('--dry-run')) {
            return;
        }

        $this->fakeInstallComposerJson('require-dev', $package, $version);
        $this->fakeInstallVendorFiles($package);
    }

    public function requireMultiple($packages, ...$extraParams)
    {
        foreach ($packages as $package => $version) {
            $this->require($package, $version, ...$extraParams);
        }
    }

    public function requireMultipleDev($packages, ...$extraParams)
    {
        foreach ($packages as $package => $version) {
            $this->requireDev($package, $version, ...$extraParams);
        }
    }

    public function remove($package)
    {
        $this->removeFromComposerJson($package);
        $this->removeFromVendorFiles($package);
    }

    public function removeDev($package)
    {
        $this->remove($package);
    }

    public function runAndOperateOnOutput($args, $callback)
    {
        $args = collect($args);

        if (! $args->contains('require')) {
            return;
        }

        $requireMethod = $args->contains('--dev')
            ? 'requireMultipleDev'
            : 'requireMultiple';

        $packages = $args
            ->filter(function ($arg) {
                return Str::contains($arg, '/');
            })
            ->mapWithKeys(function ($arg) {
                $parts = explode(':', $arg);

                return [$parts[0] => $parts[1]];
            })
            ->all();

        $this->{$requireMethod}($packages);
    }

    private function fakeInstallComposerJson($requireKey, $package, $version)
    {
        $composerJson = json_decode($this->files->get(base_path('composer.json')), true);

        $composerJson[$requireKey][$package] = $version ?? '*';

        $this->files->put(
            base_path('composer.json'),
            json_encode($composerJson, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        );
    }

    private function removeFromComposerJson($package)
    {
        $composerJson = json_decode($this->files->get(base_path('composer.json')), true);

        Arr::forget($composerJson, "require.{$package}");
        Arr::forget($composerJson, "require-dev.{$package}");

        $this->files->put(
            base_path('composer.json'),
            json_encode($composerJson, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        );
    }

    private function fakeInstallVendorFiles($package)
    {
        if ($this->files->exists($path = base_path("vendor/{$package}"))) {
            $this->files->deleteDirectory($path);
        }

        if ($package === 'statamic/cool-runnings') {
            $this->files->copyDirectory(base_path('repo/cool-runnings'), $path);
        } else {
            $this->files->makeDirectory($path, 0755, true);
        }
    }

    private function removeFromVendorFiles($package)
    {
        if ($this->files->exists($path = base_path("vendor/{$package}"))) {
            $this->files->deleteDirectory($path);
        }
    }

    public function __call($method, $args)
    {
        return $this;
    }
}
