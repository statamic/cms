<?php

namespace Statamic\Testing;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Extend\Manifest;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Statamic;

abstract class TestCase extends OrchestraTestCase
{
//    protected $addon = [
//        'provider' => '',
//        'name' => 'vendor/addon',
//        'namespace' => 'Vendor\\Addon\\',
//    ];

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
    }
}
