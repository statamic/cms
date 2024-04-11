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

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMix();
        $this->withoutVite();

        Version::shouldReceive('get')->zeroOrMoreTimes()->andReturn(Composer::create(__DIR__.'/../')->installedVersion(Statamic::PACKAGE));
        $this->addToAssertionCount(-1);

        \Statamic\Facades\CP\Nav::shouldReceive('build')->zeroOrMoreTimes()->andReturn(collect());
        $this->addToAssertionCount(-1); // Dont want to assert this
    }

    protected function getPackageProviders($app)
    {
        $serviceProviders = [
            StatamicServiceProvider::class,
            $this->addonServiceProvider,
        ];

        if (class_exists('Rebing\GraphQL\GraphQLServiceProvider')) {
            array_unshift($serviceProviders, 'Rebing\GraphQL\GraphQLServiceProvider');
        }

        return $serviceProviders;
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

        $app['config']->set('statamic.users.repository', 'file');
    }
}
