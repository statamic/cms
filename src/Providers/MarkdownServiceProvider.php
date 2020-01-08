<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;
use League\CommonMark\Ext\Table\TableExtension;
use Statamic\Facades\Markdown;
use Statamic\Markdown\Manager;
use Statamic\Markdown\Parser;
use Webuni\CommonMark\AttributesExtension\AttributesExtension;

class MarkdownServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Manager::class, function () {
            return new Manager(new Parser);
        });
    }

    public function boot()
    {
        Markdown::extend(function () {
            return [
                new TableExtension,
                new AttributesExtension,
            ];
        });
    }
}
