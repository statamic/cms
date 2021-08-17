<?php

namespace Statamic\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Message;
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

    private $uris;

    public function handle()
    {
        $this->line('Please wait. This may take a while if you have a lot of content.');

        $this->comment('Warming the static cache...');

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
        ]);

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

            if ($response->getStatusCode() == 500) {
                $message .= "\n".Message::bodySummary($response, 500);
            }
        } else {
            $message = $reason->getMessage();
        }

        $this->crossLine("$uri → <comment>$message</comment>");
    }

    private function getRelativeUri(int $index): string
    {
        return Str::start(Str::after($this->uris()->get($index), config('app.url')), '/');
    }

    private function requests()
    {
        return $this->uris()->map(function ($uri) {
            return new Request('GET', $uri);
        })->all();
    }

    private function uris(): Collection
    {
        if (! $this->uris) {
            $this->uris = collect()
                ->merge($this->entries())
                ->merge($this->terms())
                ->merge($this->scopedTerms())
                ->merge($this->customRoutes())
                ->unique()
                ->sort()
                ->values();
        }

        return $this->uris;
    }

    protected function entries(): Collection
    {
        $this->comment('- Collecting entry URLs...');

        return Facades\Entry::all()->map(function (Entry $entry) {
            if (! $entry->published() || $entry->private()) {
                return null;
            }

            return $entry->absoluteUrl();
        })->filter();
    }

    protected function terms(): Collection
    {
        $this->comment('- Collecting term URLs...');

        return Facades\Term::all()
            ->map->absoluteUrl();
    }

    protected function scopedTerms(): Collection
    {
        $this->comment('- Collecting scoped term URLs...');

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
        $this->comment('- Collecting custom route URLs...');

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
