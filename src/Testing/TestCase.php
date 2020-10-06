<?php

namespace Statamic\Testing;

use Illuminate\Encryption\Encrypter;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Extend\Manifest;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Statamic;

abstract class TestCase extends OrchestraTestCase
{
    use PreventSavingStacheItemsToDisk;

    protected $shouldFakeVersion = true;
    protected $shouldPreventNavBeingBuilt = true;

    public function setUp(): void
    {
        parent::setUp();

        if ($this->shouldFakeVersion) {
            \Facades\Statamic\Version::shouldReceive('get')->andReturn('3.0.0-testing');
            $this->addToAssertionCount(-1); // Dont want to assert this
        }

        if ($this->shouldPreventNavBeingBuilt) {
            \Statamic\Facades\CP\Nav::shouldReceive('build')->andReturn([]);
            $this->addToAssertionCount(-1); // Dont want to assert this
        }

        $this->preventSavingStacheItemsToDisk();
    }

    public function tearDown(): void
    {
        $this->deleteFakeStacheDirectory();
    }

    protected function getPackageProviders($app)
    {
        return [
            StatamicServiceProvider::class,
            $this->addon['provider'],
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

        $app->make(Manifest::class)->manifest = [
            $this->addon['name'] => [
                'id'        => $this->addon['name'],
                'namespace' => $this->addon['namespace'],
            ],
        ];
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $configs = [
            'amp', 'api', 'assets', 'cp', 'editions', 'forms', 'git', 'live_preview', 'oauth',
            'protect', 'revisions', 'routes', 'search', 'sites', 'stache', 'static_caching',
            'system', 'users',
        ];

        foreach ($configs as $config) {
            $app['config']->set("statamic.$config", require(__DIR__."/../../config/{$config}.php"));
        }

        $app['config']->set('app.key', 'base64:'.base64_encode(
            Encrypter::generateKey($app['config']['app.cipher'])
        ));

        $app['config']->set('statamic.users.repository', 'file');
    }

    public function enablePro()
    {
        app()['config']->set('statamic.editions.pro', true);
    }
}
