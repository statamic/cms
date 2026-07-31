<?php

namespace Statamic\Search;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Statamic\Contracts\Search\Searchable;
use Statamic\Facades\Search;
use Statamic\Search\Searchables\Providers;
use Statamic\Support\Str;

class InsertMultipleJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of seconds the job can run before timing out.
     *
     * This needs to stay a property. Queue::createObjectPayload() reads it through
     * getAttributeValue($job, Timeout::class, 'timeout'), which - unlike maxTries
     * and backoff - has no method_exists() fallback, so a timeout() method here
     * would be silently ignored.
     */
    public ?int $timeout = null;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $name,
        public ?string $locale,
        public Collection|LazyCollection $documents,
    ) {
        $this->onConnection($connection = config('statamic.search.queue_connection', config('queue.default')));
        $this->onQueue(config('statamic.search.queue', config("queue.connections.{$connection}.queue")));

        if (filled($timeout = config('statamic.search.queue_timeout'))) {
            $this->timeout = (int) $timeout;
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $providers = app(Providers::class);
        $index = Search::index($this->name, $this->locale);

        $documents = $this->documents
            ->groupBy(fn ($document) => explode('::', $document)[0])
            ->flatMap(function ($documents, $prefix) use ($providers) {
                return $providers
                    ->getByPrefix($prefix)
                    ->find($documents->map(fn ($reference) => Str::after($reference, '::'))->all())
                    ->all();
            })
            ->mapWithKeys(function (Searchable $item) use ($index) {
                return [$item->getSearchReference() => $index->fields($item)];
            });

        $index->insertDocuments(new Documents($documents));
    }
}
