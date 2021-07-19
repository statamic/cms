<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Statamic\Console\EnhancesCommands;
use Statamic\Console\RunsInPlease;
use Statamic\Entries\Collection as EntriesCollection;
use Statamic\Entries\Entry;
use Statamic\Facades;
use Statamic\Facades\URL;
use Statamic\Http\Controllers\FrontendController;
use Statamic\Support\Str;
use Statamic\Taxonomies\Taxonomy;

class StaticWarm extends Command
{
    use RunsInPlease;
    use EnhancesCommands;

    protected $name = 'statamic:static:warm';
    protected $description = "Warms the static cache by visiting all URL's.";

    public function handle()
    {
        $this->info('Warming the static cache.');

        $this->warm();

        return 0;
    }

    private function warm(): void
    {
        Http::pool(function ($pool) {
            return $this->requests($pool);
        });
    }

    private function requests($pool): array
    {
        return $this->uris()->map(function ($uri) use ($pool) {
            $pool->get($uri)->then(function ($response) use ($uri) {
                $this->outputResponseLine($uri, $response);
            });
        })->all();
    }

    private function outputResponseLine(string $uri, Response $response): void
    {
        $response->ok() ? $this->checkLine($uri) : $this->crossLine($uri);
    }

    private function uris(): Collection
    {
        return collect()
            ->merge($this->entries())
            ->merge($this->terms())
            ->merge($this->scopedTerms())
            ->merge($this->customRoutes())
            ->unique()
            ->values();
    }

    protected function entries(): Collection
    {
        return Facades\Entry::all()
            ->reject(function (Entry $entry) {
                return is_null($entry->uri());
            })
            ->filter(function (Entry $entry) {
                return $entry->published();
            })
            ->map->absoluteUrl();
    }

    protected function terms(): Collection
    {
        return Facades\Term::all()
            ->map->absoluteUrl();
    }

    protected function scopedTerms(): Collection
    {
        return Facades\Collection::all()
            ->flatMap(function (EntriesCollection $collection) {
                return $this->getCollectionTerms($collection);
            })
            ->map->absoluteUrl();
    }

    protected function getCollectionTerms($collection)
    {
        return $collection->taxonomies()
            ->flatMap(function (Taxonomy $taxonomy) {
                return $taxonomy->queryTerms()->get();
            })
            ->map->collection($collection);
    }

    protected function customRoutes(): Collection
    {
        $action = FrontendController::class.'@route';

        return collect(app('router')->getRoutes()->getRoutes())
            ->filter(function (Route $route) use ($action) {
                return $route->getActionName() === $action && ! Str::contains($route->uri(), '{');
            })
            ->map(function (Route $route) {
                return URL::tidy(Str::start($route->uri(), config('app.url').'/'));
            });
    }
}
