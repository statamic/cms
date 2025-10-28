<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;
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
    }
}
