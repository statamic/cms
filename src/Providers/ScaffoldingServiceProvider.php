<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;
use Statamic\View\Scaffolding\Fieldtypes\Variables\DictionaryVariables;

class ScaffoldingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(DictionaryVariables::class);

        $hasLoaded = false;

        $this->app->afterResolving(DictionaryVariables::class, function (DictionaryVariables $variables) use (&$hasLoaded) {
            if ($hasLoaded) {
                return;
            }

            $variables->register('countries', [
                'name', 'iso3', 'iso2', 'region', 'subregion', 'emoji',
            ])->register('currencies', [
                'code', 'name', 'symbol', 'decimals',
            ])->register('languages', [
                'code', 'name',
            ])->register('locales', [
                'name',
            ])->register('timezones', [
                'name', 'offset',
            ]);

            $hasLoaded = true;
        });
    }
}
