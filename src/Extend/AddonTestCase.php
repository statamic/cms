<?php

namespace Statamic\Extend;

use Facades\Statamic\Marketplace\Marketplace;
use Facades\Statamic\Version;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Console\Processes\Composer;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Statamic;

abstract class AddonTestCase extends OrchestraTestCase
{
    protected string $addonServiceProvider;

    public function setUp(): void
    {
        parent::setUp();

        $this->withoutMix();
        $this->withoutVite();

        Version::shouldReceive('get')->andReturn(Composer::create(__DIR__.'/../')->installedVersion(Statamic::PACKAGE));
        $this->addToAssertionCount(-1);
    }

    protected function getPackageProviders($app)
    {
        // TODO: When the GraphQL package is installed, register the service provider here.
        return [
            StatamicServiceProvider::class,
            $this->addonServiceProvider,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Statamic' => Statamic::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $path = dirname((new \ReflectionClass($this->addonServiceProvider))->getFileName()).'/../composer.json';
        $composerJson = $app['files']->get($path);

        $package = json_decode($composerJson, true);
        $package['version'] = 'dev-main';

        // TODO: Ideally, we shouldn't be mocking stuff in the TestCase.
        Marketplace::shouldReceive('package')
            ->once()
            ->with($package['name'], $package['version'])
            ->andReturn([
                'id' => null,
                'slug' => null,
                'url' => null,
                'seller' => null,
                'latest_version' => null,
            ]);

        $app->make(Manifest::class)->manifest = [
            $package['name'] => app(Manifest::class)->formatPackage($package),
        ];
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        // TODO: Just grab all the files.
        $configs = [
            'assets', 'cp', 'forms', 'static_caching',
            'sites', 'stache', 'system', 'users',
        ];

        foreach ($configs as $config) {
            $app['config']->set("statamic.$config", require (__DIR__."/../../config/{$config}.php"));
        }

        $app['config']->set('statamic.users.repository', 'file');
    }
}
