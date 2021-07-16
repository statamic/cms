<?php

namespace Statamic\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Statamic\Console\EnhancesCommands;
use Statamic\Console\RunsInPlease;
use Statamic\Entries\Collection as EntriesCollection;
use Statamic\Entries\Entry as Entry;
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
        $client = new Client();

        $uris = $this->uris();

        $pool = new Pool($client, $this->requests(), [
            'fulfilled' => function (Response $response, $index) use ($uris) {
                $this->checkLine($uris->get($index));
            },
            'rejected' => function (RequestException $e, $index) use ($uris) {
                $this->crossLine($uris->get($index));
            },
        ]);

        $promise = $pool->promise();

        $promise->wait();
    }

    private function requests(): array
    {
        return $this->uris()->map(function ($uri) {
            return new Request('GET', $uri);
        })->all();
    }

    private function uris(): Collection
    {
        return collect()
            ->merge($this->customRoutes())
            ->merge($this->entries())
            ->merge($this->terms())
            ->merge($this->scopedTerms()) // TODO Obsolete now that all terms have are saved as a file?
            ->unique()
            ->values();
    }

    protected function customRoutes(): Collection
    {
        // TODO test
        return collect(app('router')->getRoutes()->getRoutes())
            ->filter(function ($route) {
                return $route->getActionName() === FrontendController::class.'@route'
                    && !Str::contains($route->uri(), '{');
            })
            ->map(function (Route $route) {
                return URL::tidy(Str::start($route->uri(), config('app.url').'/'));
            });
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

    // TODO Obsolete?
    protected function scopedTerms(): Collection
    {
        return Facades\Collection::all()
            ->flatMap(function (EntriesCollection $collection) {
                return $this->getCollectionTerms($collection);
            })
            ->map->absoluteUrl();
    }

    // TODO Obsolete?
    protected function getCollectionTerms($collection)
    {
        return $collection->taxonomies()
            ->flatMap(function (Taxonomy $taxonomy) {
                return $taxonomy->queryTerms()->get();
            })
            ->map->collection($collection);
    }
}
