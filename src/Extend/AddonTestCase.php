<?php

namespace Statamic\Extend;

use Facades\Statamic\Version;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use ReflectionClass;
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

        $reflector = new ReflectionClass($this->addonServiceProvider);
        $directory = dirname($reflector->getFileName());

        $providerParts = explode('\\', $this->addonServiceProvider, -1);
        $namespace = implode('\\', $providerParts);

        $json = json_decode($app['files']->get($directory.'/../composer.json'), true);
        $statamic = $json['extra']['statamic'] ?? [];
        $autoload = $json['autoload']['psr-4'][$namespace.'\\'];

        $app->make(Manifest::class)->manifest = [
            $json['name'] => [
                'id' => $json['name'],
                'slug' => $statamic['slug'] ?? null,
                'version' => 'dev-main',
                'namespace' => $namespace,
                'autoload' => $autoload,
                'provider' => $this->addonServiceProvider,
            ],
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
