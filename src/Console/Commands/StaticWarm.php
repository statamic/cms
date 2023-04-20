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
use Statamic\StaticCaching\Cacher as StaticCacher;
use Statamic\Support\Str;
use Statamic\Taxonomies\LocalizedTerm;
use Statamic\Taxonomies\Taxonomy;

class StaticWarm extends Command
{
    use RunsInPlease;
    use EnhancesCommands;

    protected $signature = 'statamic:static:warm
        {--queue : Queue the requests}
        {--u|user= : HTTP authentication user}
        {--p|password= : HTTP authentication password}
    ';

    protected $description = 'Warms the static cache by visiting all URLs';

    protected $shouldQueue = false;

    private $uris;

    public function handle()
    {
        if (! config('statamic.static_caching.strategy')) {
            $this->error('Static caching is not enabled.');

            return 1;
        }

        $this->shouldQueue = $this->option('queue');

        if ($this->shouldQueue && config('queue.default') === 'sync') {
            $this->error('The queue connection is set to "sync". Queueing will be disabled.');
            $this->shouldQueue = false;
        }

        $this->comment('Please wait. This may take a while if you have a lot of content.');

        $this->warm();

        $this->output->newLine();
        $this->info($this->shouldQueue
            ? 'All requests to warm the static cache have been added to the queue.'
            : 'The static cache has been warmed.'
        );

        return 0;
    }

    private function warm(): void
    {
        $client = new Client([
            'verify' => ! $this->laravel->isLocal(),
            'auth' => $this->option('user') && $this->option('password')
                ? [$this->option('user'), $this->option('password')]
                : null,
        ]);

        $this->output->newLine();
        $this->line('Compiling URLs...');

        $requests = $this->requests();

        $this->output->newLine();

        if ($this->shouldQueue) {
            $queue = config('statamic.static_caching.warm_queue');
            $this->line(sprintf('Adding %s requests onto %squeue...', count($requests), $queue ? $queue.' ' : ''));

            foreach ($requests as $request) {
                StaticWarmJob::dispatch($request)->onQueue($queue);
            }
        } else {
            $this->line('Visiting '.count($requests).' URLs...');

            $pool = new Pool($client, $requests, [
                'concurrency' => $this->concurrency(),
                'fulfilled' => [$this, 'outputSuccessLine'],
                'rejected' => [$this, 'outputFailureLine'],
            ]);

            $promise = $pool->promise();

            $promise->wait();
        }
    }

    private function concurrency(): int
    {
        $strategy = config('statamic.static_caching.strategy');

        return config("statamic.static_caching.strategies.$strategy.warm_concurrency", 25);
    }

    public function outputSuccessLine(Response $response, $index): void
    {
        $this->checkLine($this->getRelativeUri($index));
    }

    public function outputFailureLine($exception, $index): void
    {
        $uri = $this->getRelativeUri($index);

        if ($exception instanceof RequestException && $exception->hasResponse()) {
            $response = $exception->getResponse();

            $message = $response->getStatusCode().' '.$response->getReasonPhrase();

            if ($response->getStatusCode() == 500) {
                $message .= "\n".Message::bodySummary($response, 500);
            }
        } else {
            $message = $exception->getMessage();
        }

        $this->crossLine("$uri → <fg=cyan>$message</fg=cyan>");
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
        if ($this->uris) {
            return $this->uris;
        }

        $cacher = app(StaticCacher::class);

        return $this->uris = collect()
            ->merge($this->entryUris())
            ->merge($this->taxonomyUris())
            ->merge($this->termUris())
            ->merge($this->customRouteUris())
            ->unique()
            ->reject(function ($uri) use ($cacher) {
                return $cacher->isExcluded($uri);
            })
            ->sort()
            ->values();
    }

    protected function entryUris(): Collection
    {
        $this->line('[ ] Entries...');

        $entries = Facades\Entry::all()->map(function (Entry $entry) {
            if (! $entry->published() || $entry->private()) {
                return null;
            }

            return $entry->absoluteUrl();
        })->filter();

        $this->line("\x1B[1A\x1B[2K<info>[✔]</info> Entries");

        return $entries;
    }

    protected function taxonomyUris(): Collection
    {
        $this->line('[ ] Taxonomies...');

        $taxonomyUris = Facades\Taxonomy::all()
            ->filter(function ($taxonomy) {
                return view()->exists($taxonomy->template());
            })
            ->flatMap(function (Taxonomy $taxonomy) {
                return $taxonomy->sites()->map(function ($site) use ($taxonomy) {
                    // Needed because Taxonomy uses the current site. If the Taxonomy
                    // class ever gets its own localization logic we can remove this.
                    Facades\Site::setCurrent($site);

                    return $taxonomy->absoluteUrl();
                });
            });

        $this->line("\x1B[1A\x1B[2K<info>[✔]</info> Taxonomies");

        return $taxonomyUris;
    }

    protected function termUris(): Collection
    {
        $this->line('[ ] Taxonomy terms...');

        $terms = Facades\Term::all()
            ->merge($this->scopedTerms())
            ->filter(function ($term) {
                return view()->exists($term->template());
            })
            ->flatMap(function (LocalizedTerm $term) {
                return $term->taxonomy()->sites()->map(function ($site) use ($term) {
                    return $term->in($site)->absoluteUrl();
                });
            });

        $this->line("\x1B[1A\x1B[2K<info>[✔]</info> Taxonomy terms");

        return $terms;
    }

    protected function scopedTerms(): Collection
    {
        return Facades\Collection::all()
            ->flatMap(function (EntriesCollection $collection) {
                return $this->getCollectionTerms($collection);
            });
    }

    protected function getCollectionTerms($collection)
    {
        return $collection->taxonomies()
            ->flatMap(function (Taxonomy $taxonomy) {
                return $taxonomy->queryTerms()->get();
            })
            ->map->collection($collection);
    }

    protected function customRouteUris(): Collection
    {
        $this->line('[ ] Custom routes...');

        $action = FrontendController::class.'@route';

        $routes = collect(app('router')->getRoutes()->getRoutes())
            ->filter(function (Route $route) use ($action) {
                return $route->getActionName() === $action && ! Str::contains($route->uri(), '{');
            })
            ->map(function (Route $route) {
                return URL::tidy(Str::start($route->uri(), config('app.url').'/'));
            });

        $this->line("\x1B[1A\x1B[2K<info>[✔]</info> Custom routes");

        return $routes;
    }
}
