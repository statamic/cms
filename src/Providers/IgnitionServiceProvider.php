<?php

namespace Statamic\Providers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use Spatie\ErrorSolutions\Contracts\SolutionProviderRepository;
use Statamic\Ignition\SolutionProviders\OAuthDisabled;
use Statamic\Ignition\SolutionProviders\UsingOldClass;

class IgnitionServiceProvider extends ServiceProvider
{
    protected $providers = [
        OAuthDisabled::class,
        UsingOldClass::class,
    ];

    public function register()
    {
        try {
            $this->app->make(SolutionProviderRepository::class)
                ->registerSolutionProviders($this->providers);
        } catch (BindingResolutionException $e) {
            //
        }
    }
}
