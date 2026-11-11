<?php

namespace Statamic\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Console\Command;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Collection;
use Statamic\Console\Commands\Concerns\NormalizesPaginationHeader;
use Statamic\Console\EnhancesCommands;
use Statamic\Console\RunsInPlease;
use Statamic\Entries\Collection as EntriesCollection;
use Statamic\Facades;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\StaticCaching\Cacher as StaticCacher;
use Statamic\StaticCaching\RecacheToken;
use Statamic\StaticCaching\WarmableUrls;
use Statamic\Support\Str;
use Statamic\Support\Traits\Hookable;

class StaticWarm extends Command
{
    use EnhancesCommands;
    use Hookable;
    use NormalizesPaginationHeader;
    use RunsInPlease;

    protected $signature = 'statamic:static:warm
        {--queue : Queue the requests}
        {--u|user= : HTTP authentication user}
        {--p|password= : HTTP authentication password}
        {--insecure : Skip SSL verification}
        {--uncached : Only warm uncached URLs}
        {--max-depth= : Maximum depth of URLs to warm}
        {--include= : Only warm specific URLs}
        {--exclude= : Exclude specific URLs}
        {--max-requests= : Maximum number of requests to warm}
        {--header=* : Set custom header (e.g. "Authorization: Bearer your_token")}
    ';

    protected $description = 'Warms the static cache by visiting all URLs';

    protected $shouldQueue = false;
    protected $queueConnection;

    private $uris;

    private $paginated = [];

    public function handle()
    {
        if (! config('statamic.static_caching.strategy')) {
            $this->components->error('Static caching is not enabled.');

            return 1;
        }

        $this->shouldQueue = $this->option('queue');
        $this->queueConnection = config('statamic.static_caching.warm_queue_connection') ?? config('queue.default');

        if ($this->shouldQueue && $this->queueConnection === 'sync') {
            $this->components->error('The queue connection is set to "sync". Queueing will be disabled.');
            $this->shouldQueue = false;
        }

        $this->comment('Please wait. This may take a while if you have a lot of content.');

        $this->warm();

        $this->components->info(
            $this->shouldQueue
                ? 'All requests to warm the static cache have been added to the queue.'
                : 'The static cache has been warmed.'
        );

        return 0;
    }

    private function warm(): void
    {
        $this->output->newLine();
        $this->line('Compiling URLs...');

        $requests = $this->requests();

        $this->output->newLine();

        if ($this->shouldQueue) {
            $queue = config('statamic.static_caching.warm_queue');
            $this->line(sprintf('Adding %s requests onto %squeue...', count($requests), $queue ? $queue.' ' : ''));

            $jobClass = $this->option('uncached')
                ? StaticWarmUncachedJob::class
                : (config('statamic.static_caching.warm_job') ?? StaticWarmJob::class);

            foreach ($requests as $request) {
                $jobClass::dispatch($request, $this->clientConfig())
                    ->onConnection($this->queueConnection)
                    ->onQueue($queue);
            }
        } else {
            $this->line('Visiting '.count($requests).' URLs...');

            $pool = new Pool($this->client(), $requests, [
                'concurrency' => $this->concurrency(),
                'fulfilled' => [$this, 'outputSuccessLine'],
                'rejected' => [$this, 'outputFailureLine'],
            ]);

            $promise = $pool->promise();

            $promise->wait();

            $this->warmPaginatedPages();
        }
    }

    /**
     * Paginated pages are only known about once their first page has responded. They're
     * warmed together afterwards, so the pool of other URLs is never left waiting on them.
     */
    private function warmPaginatedPages(): void
    {
        $urls = $this->paginatedUrls();

        if ($urls->isEmpty()) {
            return;
        }

        $headers = $this->parseHeaders($this->option('header'));

        $requests = $urls->map(fn (string $url) => new Request('GET', $url, $headers))->all();

        $pool = new Pool($this->client(), $requests, [
            'concurrency' => $this->concurrency(),
            'fulfilled' => function (Response $response, $index) use ($urls) {
                $this->components->twoColumnDetail($this->getRelativeUri($urls->get($index)), '<info>✓ Cached</info>');
            },
            'rejected' => function ($exception, $index) use ($urls) {
                $this->outputFailureLineFor($urls->get($index), $exception);
            },
        ]);

        $promise = $pool->promise();

        $promise->wait();
    }

    private function concurrency(): int
    {
        $strategy = config('statamic.static_caching.strategy');

        return config("statamic.static_caching.strategies.$strategy.warm_concurrency", 25);
    }

    private function client(): Client
    {
        return app(Client::class, ['config' => $this->clientConfig()]);
    }

    private function clientConfig(): array
    {
        return [
            'verify' => $this->shouldVerifySsl(),
            'auth' => $this->option('user') && $this->option('password')
                ? [$this->option('user'), $this->option('password')]
                : null,
        ];
    }

    public function outputSuccessLine(Response $response, $index): void
    {
        $this->components->twoColumnDetail($this->getRelativeUri($this->uris()->get($index)), '<info>✓ Cached</info>');

        if ($response->hasHeader('X-Statamic-Pagination')) {
            $this->paginated[] = [$this->uris()->get($index), ...$this->paginationHeader($response)];
        }
    }

    private function paginatedUrls(): Collection
    {
        $cacher = app(StaticCacher::class);

        return collect($this->paginated)->flatMap(function ($paginated) use ($cacher) {
            [$url, $currentPage, $totalPages, $pageName] = $paginated;

            $cachedAs = $cacher->getUrl(HttpRequest::create($url));

            return collect(range($currentPage, $totalPages))
                ->map(fn ($page) => "{$url}?{$pageName}={$page}")
                // When query strings are ignored, a page is cached under the same URL
                // as the one that has just been warmed, so there's nothing left to warm.
                ->reject(fn ($pageUrl) => $cacher->getUrl(HttpRequest::create($pageUrl)) === $cachedAs)
                ->map(function ($pageUrl) {
                    return config('statamic.static_caching.background_recache', false)
                        ? RecacheToken::addToUrl($pageUrl)
                        : $pageUrl;
                });
        })->values();
    }

    public function outputFailureLine($exception, $index): void
    {
        $this->outputFailureLineFor($this->uris()->get($index), $exception);
    }

    private function outputFailureLineFor(string $url, $exception): void
    {
        $uri = $this->getRelativeUri($url);

        if ($exception instanceof RequestException && $exception->hasResponse()) {
            $response = $exception->getResponse();

            $message = $response->getStatusCode().' '.$response->getReasonPhrase();

            if ($response->getStatusCode() == 500) {
                $message .= "\n".Message::bodySummary($response, 500);
            }
        } else {
            $message = $exception->getMessage();
        }

        $this->components->twoColumnDetail($uri, "<fg=cyan>$message</fg=cyan>");
    }

    private function getRelativeUri(string $uri): string
    {
        return Str::start(Str::after($uri, config('app.url')), '/');
    }

    private function requests()
    {
        $headers = $this->parseHeaders($this->option('header'));

        return $this->uris()->map(function ($uri) use ($headers) {
            if (config('statamic.static_caching.background_recache', false)) {
                if (substr_count($uri, '/') == 2) {
                    $uri .= '/';
                }

                $uri = RecacheToken::addToUrl($uri);
            }

            return new Request('GET', $uri, $headers);
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
            ->merge($this->additionalUris())
            ->unique()
            ->filter(fn ($uri) => $this->shouldInclude($uri))
            ->reject(fn ($uri) => $this->shouldExclude($uri))
            ->reject(fn ($uri) => $this->exceedsMaxDepth($uri))
            ->reject(function ($uri) use ($cacher) {
                if ($this->option('uncached') && $cacher->hasCachedPage(HttpRequest::create($uri))) {
                    return true;
                }

                Site::resolveCurrentUrlUsing(fn () => $uri);

                return $cacher->isExcluded($uri);
            })
            ->sort()
            ->values()
            ->when($this->option('max-requests'), fn ($uris, $max) => $uris->take($max));
    }

    private function shouldInclude($uri): bool
    {
        if (! $inclusions = $this->option('include')) {
            return true;
        }

        $inclusions = explode(',', $inclusions);

        return collect($inclusions)->contains(fn ($included) => $this->uriMatches($uri, $included));
    }

    private function shouldExclude($uri): bool
    {
        if (! $exclusions = $this->option('exclude')) {
            return false;
        }

        $exclusions = explode(',', $exclusions);

        return collect($exclusions)->contains(fn ($excluded) => $this->uriMatches($uri, $excluded));
    }

    private function uriMatches($uri, $pattern): bool
    {
        $uri = URL::makeRelative($uri);

        if (Str::endsWith($pattern, '*') && Str::startsWith($uri, Str::removeRight($pattern, '*'))) {
            return true;
        } elseif (URL::tidy($uri, '/') === URL::tidy($pattern, '/')) {
            return true;
        }

        return false;
    }

    private function exceedsMaxDepth($uri): bool
    {
        if (! $max = $this->option('max-depth')) {
            return false;
        }

        return count(explode('/', trim(URL::makeRelative($uri), '/'))) > $max;
    }

    private function shouldVerifySsl(): bool
    {
        if ($this->option('insecure') || config('statamic.static_caching.warm_insecure')) {
            return false;
        }

        return ! $this->laravel->isLocal();
    }

    protected function entryUris(): Collection
    {
        $this->line('[ ] Entries...');

        $entries = app(WarmableUrls::class)->entries();

        $this->line("\x1B[1A\x1B[2K<info>[✔]</info> Entries");

        return $entries;
    }

    protected function taxonomyUris(): Collection
    {
        $this->line('[ ] Taxonomies...');

        $taxonomyUris = app(WarmableUrls::class)->taxonomies();

        $this->line("\x1B[1A\x1B[2K<info>[✔]</info> Taxonomies");

        return $taxonomyUris;
    }

    protected function termUris(): Collection
    {
        $this->line('[ ] Taxonomy terms...');

        $terms = app(WarmableUrls::class)->terms($this->scopedTerms());

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
        return app(WarmableUrls::class)->collectionTerms($collection);
    }

    protected function customRouteUris(): Collection
    {
        $this->line('[ ] Custom routes...');

        $routes = app(WarmableUrls::class)->customRoutes();

        $this->line("\x1B[1A\x1B[2K<info>[✔]</info> Custom routes");

        return $routes;
    }

    protected function additionalUris(): Collection
    {
        $this->line('[ ] Additional...');

        $uris = $this->additionalUrls();

        $this->line("\x1B[1A\x1B[2K<info>[✔]</info> Additional");

        return $uris;
    }

    public function additionalUrls(): Collection
    {
        return $this->runHooks('additional', collect())->map(fn ($uri) => URL::makeAbsolute($uri));
    }

    private function parseHeaders($headerOptions): array
    {
        $headers = [];
        if (empty($headerOptions)) {
            return $headers;
        }
        if (! is_array($headerOptions)) {
            $headerOptions = [$headerOptions];
        }
        foreach ($headerOptions as $header) {
            if (strpos($header, ':') !== false) {
                [$key, $value] = explode(':', $header, 2);
                $headers[trim($key)] = trim($value);
            } else {
                $this->line("<fg=yellow;options=bold>Warning:</> Invalid header format: '$header'. Headers should be in 'Key: Value' format.");
            }
        }

        return $headers;
    }
}
