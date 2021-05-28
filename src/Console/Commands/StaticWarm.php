<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Application;
use Illuminate\Http\Client\Pool;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Statamic\Console\RunsInPlease;
use Statamic\Entries\Collection as EntriesCollection;
use Statamic\Entries\Entry as Entry;
use Statamic\Facades\Collection as CollectionAPI;
use Statamic\Facades\Entry as EntryAPI;
use Statamic\Facades\Term as TermAPI;
use Statamic\Facades\URL;
use Statamic\Http\Controllers\FrontendController;
use Statamic\Support\Str;
use Statamic\Taxonomies\Taxonomy;

class StaticWarm extends Command
class StaticWarm extends Command
{
    use RunsInPlease;

    protected $name = 'statamic:static:warm';
    protected $description = "Warms the static cache by visiting all URL's.";

    public function handle()
    {
        $this->checkVersion();

        $this->info('Warming the static cache.');

        $this->warm();

        return 0;
    }

    private function warm(): void
    {
        Http::pool(fn (Pool $pool) => $this->routes()
            ->map(fn (string $url) => $pool->get($url)));
    }

    private function routes(): Collection
    {
        return collect()
            ->merge($this->customRoutes())
            ->merge($this->entries())
            ->merge($this->terms())
            ->merge($this->scopedTerms())
            ->values()
            ->unique();
    }

    protected function customRoutes(): Collection
    {
        return collect(app('router')->getRoutes()->getRoutes())
            ->filter(fn ($route) => $route->getActionName() === FrontendController::class.'@route' && ! Str::contains($route->uri(), '{'))
            ->map(fn (Route $route) => URL::tidy(Str::start($route->uri(), config('app.url').'/')));
    }

    protected function entries(): Collection
    {
        return EntryAPI::all()
            ->reject(fn (Entry $entry) => is_null($entry->uri()))
            ->filter(fn (Entry $entry) => $entry->published())
            ->map
            ->absoluteUrl();
    }

    protected function terms(): Collection
    {
        return TermAPI::all()
            ->map
            ->absoluteUrl();
    }

    protected function scopedTerms(): Collection
    {
        return CollectionAPI::all()
            ->flatMap(fn (EntriesCollection $collection) => $this->getCollectionTerms($collection))
            ->map
            ->absoluteUrl();
    }

    protected function getCollectionTerms($collection)
    {
        return $collection
            ->taxonomies()
            ->flatMap(fn (Taxonomy $taxonomy) => $taxonomy->queryTerms()->get())
            ->map
            ->collection($collection);
    }

    private function checkVersion(): void
    {
        throw_if(
            version_compare(Application::VERSION, '8.42.2', '<'),
            new \RuntimeException('To use this, you must be on PHP 8 & Laravel 8.37+')
        );
    }
}
