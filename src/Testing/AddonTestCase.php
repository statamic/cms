<?php

namespace Statamic\Testing;

use Facades\Statamic\Version;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use ReflectionClass;
use Statamic\Console\Processes\Composer;
use Statamic\Extend\Manifest;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Statamic;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

abstract class AddonTestCase extends OrchestraTestCase
{
    protected string $addonServiceProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMix();
        $this->withoutVite();

        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[PreventsSavingStacheItemsToDisk::class])) {
            $reflection = new ReflectionClass($this);
            $this->fakeStacheDirectory = Str::before(dirname($reflection->getFileName()), DIRECTORY_SEPARATOR.'tests').'/tests/__fixtures__/dev-null';

            $this->preventSavingStacheItemsToDisk();
        }

        Version::shouldReceive('get')->zeroOrMoreTimes()->andReturn(Composer::create(__DIR__.'/../')->installedVersion(Statamic::PACKAGE));
        $this->addToAssertionCount(-1);

        \Statamic\Facades\CP\Nav::shouldReceive('build')->zeroOrMoreTimes()->andReturn(collect());
        $this->addToAssertionCount(-1); // Dont want to assert this
    }

    protected function tearDown(): void
    {
        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[PreventsSavingStacheItemsToDisk::class])) {
            $this->deleteFakeStacheDirectory();
        }

        parent::tearDown();
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

        $app['config']->set('statamic.stache.watcher', false);
        $app['config']->set('statamic.stache.stores.taxonomies.directory', $directory.'/../tests/__fixtures__/content/taxonomies');
        $app['config']->set('statamic.stache.stores.terms.directory', $directory.'/../tests/__fixtures__/content/taxonomies');
        $app['config']->set('statamic.stache.stores.collections.directory', $directory.'/../tests/__fixtures__/content/collections');
        $app['config']->set('statamic.stache.stores.entries.directory', $directory.'/../tests/__fixtures__/content/collections');
        $app['config']->set('statamic.stache.stores.navigation.directory', $directory.'/../tests/__fixtures__/content/navigation');
        $app['config']->set('statamic.stache.stores.globals.directory', $directory.'/../tests/__fixtures__/content/globals');
        $app['config']->set('statamic.stache.stores.global-variables.directory', $directory.'/../tests/__fixtures__/content/globals');
        $app['config']->set('statamic.stache.stores.asset-containers.directory', $directory.'/../tests/__fixtures__/content/assets');
        $app['config']->set('statamic.stache.stores.nav-trees.directory', $directory.'/../tests/__fixtures__/content/structures/navigation');
        $app['config']->set('statamic.stache.stores.collection-trees.directory', $directory.'/../tests/__fixtures__/content/structures/collections');
        $app['config']->set('statamic.stache.stores.form-submissions.directory', $directory.'/../tests/__fixtures__/content/submissions');
        $app['config']->set('statamic.stache.stores.users.directory', $directory.'/../tests/__fixtures__/users');
    }
}
