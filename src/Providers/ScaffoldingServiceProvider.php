<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;
use Statamic\View\Scaffolding\Fieldtypes\Variables\DictionaryVariables;
use Statamic\View\Scaffolding\TemplateGenerator;

class ScaffoldingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(TemplateGenerator::class, function () {
            return (new TemplateGenerator)
                ->withCoreGenerators()
                ->templateLanguage(config('statamic.templates.language', 'antlers'))
                ->indentType(config('statamic.templates.style.indent_type', 'space'))
                ->indentSize(config('statamic.templates.style.indent_size', 4))
                ->finalNewline(config('statamic.templates.style.final_newline', false))
                ->preferComponentSyntax(config('statamic.templates.antlers.use_components', false));
        });

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
