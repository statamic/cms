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
    protected $description = 'Warms the static cache by visiting all URLs.';

    public function handle()
    {
        $this->line('Warming the static cache...');

        $this->warm();

        $this->info('The static cache has been warmed.');

        return 0;
    }

    private function warm(): void
    {
        $client = new Client();

        $pool = new Pool($client, $this->requests(), [
            'fulfilled' => [$this, 'outputSuccessLine'],
            'rejected' => [$this, 'outputFailureLine'],
        ],);

        $promise = $pool->promise();

        $promise->wait();
    }

    public function outputSuccessLine(Response $response, $index): void
    {
        $this->checkLine($this->getRelativeUri($index));
    }

    public function outputFailureLine(RequestException $reason, $index): void
    {
        $uri = $this->getRelativeUri($index);

        if ($reason->hasResponse()) {
            $response = $reason->getResponse();
            $message = $response->getStatusCode().' '.$response->getReasonPhrase();
        } else {
            $message = $reason->getMessage();
        }

        $this->crossLine("$uri → <comment>$message</comment>");
    }

    private function getRelativeUri(int $index): string
    {
        return Str::start(Str::after($this->uris()->get($index), config('app.url')), '/');
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
            ->merge($this->entries())
            ->merge($this->terms())
            ->merge($this->scopedTerms())
            ->merge($this->customRoutes())
            ->unique()
            ->sort(SORT_NATURAL)
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
