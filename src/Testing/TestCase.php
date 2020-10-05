<?php

namespace Statamic\Testing;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Statamic;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        // TODO: figure out how to also put the addon's provider in here too

        return [
            StatamicServiceProvider::class,
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

        // TODO: figure out how to get the addon's details

        // $app->make(Manifest::class)->manifest = [
        //     'vendor/package' => [
        //         'id'        => 'vendor/package',
        //         'namespace' => 'Vendor\\Package\\',
        //     ],
        // ];
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
            $app['config']->set("statamic.$config", require(__DIR__.'/../../config/{$config}.php'));
        }
    }
}
