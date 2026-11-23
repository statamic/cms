<?php

namespace Statamic\Search\Typesense;

use Http\Client\Exception as HttpClientException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Psr\Http\Client\ClientExceptionInterface as PsrClientException;
use Statamic\Contracts\Search\Searchable;
use Statamic\Facades\Blink;
use Statamic\Facades\Site;
use Statamic\Search\Documents;
use Statamic\Search\Index as BaseIndex;
use Statamic\Search\IndexNotFoundException;
use Statamic\Search\Result;
use Statamic\Search\Searchables\Providers;
use Statamic\Support\Str;
use Throwable;
use Typesense\Client;
use Typesense\Exceptions\ObjectNotFound;
use Typesense\Exceptions\TypesenseClientError;

class Index extends BaseIndex
{
    private Normalizer $normalizer;
    private Schema $schema;

    public function __construct(
        private Client $client,
        $name,
        array $config,
        ?string $locale = null,
    ) {
        parent::__construct($name, $config, $locale);

        $this->normalizer = new Normalizer;
        $this->schema = new Schema($client, $this->name, $config);
    }

    public function search($query)
    {
        return (new Query($this))->query($query);
    }

    public function client(): Client
    {
        return $this->client;
    }

    public function schema(): Schema
    {
        return $this->schema;
    }

    public function fields(Searchable $searchable)
    {
        return $this->normalizer->document(array_merge(
            parent::fields($searchable),
            $this->metadata($searchable),
        ));
    }

    public function insertDocuments(Documents $documents)
    {
        $this->importDocuments($this->name, $documents);
    }

    public function delete($document)
    {
        try {
            $this->collection()->documents[$this->documentId($document->getSearchReference())]->delete();
        } catch (ObjectNotFound) {
            // The document or collection has already gone.
        } catch (HttpClientException|PsrClientException $e) {
            throw $this->connectionException($e);
        }

        $this->forgetCachedState();
    }

    public function exists()
    {
        return Blink::once($this->existsBlinkKey(), fn () => $this->activeCollectionName() !== null);
    }

    public function update()
    {
        $this->ensureNaturalLanguageModel();

        if (Arr::get($this->config, 'settings.reindex', 'alias') === 'in_place') {
            return $this->updateInPlace();
        }

        return $this->updateUsingAlias();
    }

    public function searchUsingApi($query, array $parameters = []): array
    {
        $defaults = Arr::get(
            $this->config,
            'settings.search_parameters',
            Arr::get($this->config, 'settings.search_options', []),
        );
        $defaultFilter = $defaults['filter_by'] ?? null;
        $requestedFilter = $parameters['filter_by'] ?? null;
        $parameters = array_merge($defaults, $parameters);

        if ($defaultFilter && $requestedFilter) {
            $parameters['filter_by'] = "({$defaultFilter}) && ({$requestedFilter})";
        }

        $parameters['q'] = $query ?? '';

        if (is_array($parameters['query_by'] ?? null)) {
            $parameters['query_by'] = implode(',', $parameters['query_by']);
        }

        if (is_string($parameters['query_by'] ?? null)) {
            $parameters['query_by'] = str_replace('->', '.', $parameters['query_by']);
        }

        if (is_array($parameters['exclude_fields'] ?? null)) {
            $parameters['exclude_fields'] = implode(',', $parameters['exclude_fields']);
        }

        if (is_string($parameters['exclude_fields'] ?? null)) {
            $parameters['exclude_fields'] = str_replace('->', '.', $parameters['exclude_fields']);
            $parameters['exclude_fields'] = $this->removeCommaSeparated(
                $parameters['exclude_fields'],
                'reference',
            );
        }

        if (blank($parameters['exclude_fields'] ?? null)) {
            unset($parameters['exclude_fields']);
        }

        if (is_array($parameters['include_fields'] ?? null)) {
            $parameters['include_fields'] = implode(',', $parameters['include_fields']);
        }

        if (is_string($parameters['include_fields'] ?? null)) {
            $parameters['include_fields'] = str_replace('->', '.', $parameters['include_fields']);
        }

        if (filled($parameters['include_fields'] ?? null)) {
            $parameters['include_fields'] = $this->appendCommaSeparated(
                $parameters['include_fields'],
                'reference',
            );
        }

        if (blank($parameters['query_by'] ?? null)) {
            unset($parameters['query_by']);
        }

        if (! isset($parameters['query_by']) && ! isset($parameters['preset'])) {
            $parameters['query_by'] = $this->schema->queryBy();
        }

        if ($embedding = Arr::get($this->config, 'settings.embedding')) {
            $field = $this->embeddingFieldName();

            if (isset($parameters['query_by'])) {
                $parameters['query_by'] = $this->appendCommaSeparated(
                    $parameters['query_by'],
                    $field,
                );
            }
            $parameters['exclude_fields'] = $this->appendCommaSeparated(
                $parameters['exclude_fields'] ?? null,
                $field,
            );

            if (isset($embedding['alpha']) && ! isset($parameters['vector_query'])) {
                $parameters['vector_query'] = sprintf(
                    '%s:([], alpha: %s)',
                    $field,
                    $embedding['alpha'],
                );
            }
        }

        $request = [
            'searches' => [
                array_merge($parameters, ['collection' => $this->name]),
            ],
        ];

        try {
            $response = $this->client->multiSearch->perform($request, []);
        } catch (TypesenseClientError $e) {
            $this->handleTypesenseException($e);
        } catch (HttpClientException|PsrClientException $e) {
            throw $this->connectionException($e);
        }

        $result = $response['results'][0] ?? [];

        if ($error = $result['error'] ?? null) {
            $this->handleTypesenseException(new TypesenseClientError(
                $error,
                (int) ($result['code'] ?? 0),
            ));
        }

        if ($naturalLanguageError = Arr::get($result, 'parsed_nl_query.error')) {
            Log::warning('Typesense Natural Language Search fell back to the original query.', [
                'index' => $this->name,
                'error' => $naturalLanguageError,
            ]);
        }

        $hits = collect($result['hits'] ?? []);

        if ($hits->isEmpty() && isset($result['grouped_hits'])) {
            $hits = collect($result['grouped_hits'])
                ->flatMap(fn (array $group) => $group['hits'] ?? []);
        }

        $total = $hits->count();

        return [
            'found' => (int) ($result['found'] ?? $total),
            'hits' => $hits->values()->map(function (array $hit, int $position) use ($total) {
                $document = $hit['document'] ?? [];
                $document['reference'] ??= $document['id'] ?? null;
                $document['search_score'] = $total - $position;
                $document['search_text_match'] = $hit['text_match'] ?? null;
                $document['search_text_match_info'] = $hit['text_match_info'] ?? null;
                $document['search_highlights'] = ($hit['highlights'] ?? [])
                    ?: ($hit['highlight'] ?? []);

                return $document;
            }),
            'raw' => $result,
        ];
    }

    public function ensureNaturalLanguageModel(): ?string
    {
        $settings = Arr::get($this->config, 'settings.natural_language');

        if (! $settings) {
            return null;
        }

        $id = $settings['model'] ?? 'statamic_'.$this->name;
        $model = $settings['model_config'] ?? null;

        if (! $model) {
            return $id;
        }

        $cacheKey = 'statamic.search.typesense.nl-model.'.hash('sha256', serialize([
            $this->cacheFingerprint(),
            $id,
            $model,
        ]));

        try {
            Cache::remember($cacheKey, now()->addDay(), function () use ($id, $model) {
                try {
                    $this->client->nlSearchModels[$id]->retrieve();
                    $this->client->nlSearchModels[$id]->update($model);
                } catch (ObjectNotFound) {
                    $this->client->nlSearchModels->create(array_merge($model, ['id' => $id]));
                }

                return true;
            });
        } catch (HttpClientException|PsrClientException $e) {
            throw $this->connectionException($e);
        }

        return $id;
    }

    public function scopedSearchKey(array $parameters = [], int $expires = 3600): string
    {
        $key = Arr::get($this->config, 'credentials.search_only_api_key')
            ?? Arr::get($this->config, 'client.search_only_api_key');

        if (! $key) {
            throw new \LogicException(
                "No Typesense search-only API key has been configured for index [{$this->name}]."
            );
        }

        $defaults = Arr::get(
            $this->config,
            'settings.search_parameters',
            Arr::get($this->config, 'settings.search_options', []),
        );
        $defaultFilter = $defaults['filter_by'] ?? null;
        $site = $this->locale;

        if (! $site && Site::hasMultiple()) {
            $site = Site::current()->handle();
        }

        $filters = array_filter([
            'status:=published',
            $site ? 'site:='.$this->escapeFilterValue($site) : null,
            $defaultFilter ? "({$defaultFilter})" : null,
        ]);

        if ($filter = $parameters['filter_by'] ?? null) {
            $filters[] = "({$filter})";
        }

        $parameters['filter_by'] = implode(' && ', $filters);
        $parameters['expires_at'] ??= now()->addSeconds($expires)->timestamp;

        if (Arr::get($this->config, 'settings.embedding')) {
            $parameters['exclude_fields'] = $this->appendCommaSeparated(
                $parameters['exclude_fields'] ?? null,
                $this->embeddingFieldName(),
            );
        }

        return $this->client->keys->generateScopedSearchKey($key, $parameters);
    }

    public function documentId(string $reference): string
    {
        return hash('sha256', $reference);
    }

    public function cacheFingerprint(): string
    {
        return hash('sha256', serialize([
            Arr::get($this->config, 'client.nodes', $this->config['nodes'] ?? []),
            Arr::get($this->config, 'client.api_key', Arr::get($this->config, 'credentials.api_key')),
        ]));
    }

    public function searchCacheFingerprint(): string
    {
        return hash('sha256', serialize([
            $this->cacheFingerprint(),
            $this->config['fields'] ?? [],
            $this->config['settings'] ?? [],
            Blink::get($this->searchVersionKey(), 0),
        ]));
    }

    public function extraAugmentedResultData(Result $result)
    {
        $raw = $result->getRawResult();
        $highlights = collect($raw['search_highlights'] ?? [])
            ->mapWithKeys(function (array $highlight, int|string $key) {
                $field = $highlight['field']
                    ?? $highlight['field_name']
                    ?? (is_string($key) ? $key : null);

                return $field ? [$field => $highlight] : [];
            })
            ->all();

        return [
            'search_highlights' => $highlights,
            'search_snippets' => collect($highlights)
                ->map(fn (array $highlight) => $highlight['snippet'] ?? $highlight['value'] ?? null)
                ->filter()
                ->all(),
            'search_text_match' => $raw['search_text_match'] ?? null,
        ];
    }

    protected function deleteIndex()
    {
        $active = $this->activeCollectionName();

        if (! $active) {
            return;
        }

        if ($active !== $this->name) {
            try {
                $this->client->aliases[$this->name]->delete();
            } catch (ObjectNotFound) {
                //
            }
        }

        try {
            $this->client->collections[$active]->delete();
        } catch (ObjectNotFound) {
            //
        }

        $this->forgetCachedState();
    }

    private function updateUsingAlias(): self
    {
        $previous = $this->activeCollectionName();
        $candidate = $this->newCollectionName();
        $legacyCollectionRemoved = false;
        $candidateCreated = false;

        try {
            $schema = $this->schema->definition($candidate);

            if ($previous) {
                $existing = $this->client->collections[$previous]->retrieve();

                foreach (['synonym_sets', 'curation_sets'] as $setting) {
                    if (! array_key_exists($setting, $schema) && array_key_exists($setting, $existing)) {
                        $schema[$setting] = $existing[$setting];
                    }
                }
            }

            $this->client->collections->create($schema);
            $candidateCreated = true;
            $this->importAllSearchablesInto($candidate);

            if ($previous === $this->name) {
                $this->client->collections[$previous]->delete();
                $legacyCollectionRemoved = true;
            }

            $this->client->aliases->upsert($this->name, [
                'collection_name' => $candidate,
            ]);
        } catch (Throwable $e) {
            $this->forgetCachedState();

            if (
                $candidateCreated
                && ! $legacyCollectionRemoved
                && $this->aliasPointsTo($candidate) === false
            ) {
                try {
                    $this->client->collections[$candidate]->delete();
                } catch (Throwable) {
                    //
                }
            }

            if ($e instanceof HttpClientException || $e instanceof PsrClientException) {
                throw $this->connectionException($e);
            }

            throw $e;
        }

        if ($previous && $previous !== $this->name && $previous !== $candidate) {
            try {
                $this->client->collections[$previous]->delete();
            } catch (Throwable $e) {
                Log::warning('The old Typesense collection could not be deleted after its alias was swapped.', [
                    'index' => $this->name,
                    'collection' => $previous,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->forgetCachedState();

        return $this;
    }

    private function aliasPointsTo(string $collection): ?bool
    {
        try {
            $active = $this->client->aliases[$this->name]->retrieve()['collection_name'] ?? null;

            return $active === $collection;
        } catch (ObjectNotFound) {
            return false;
        } catch (Throwable) {
            return null;
        }
    }

    private function updateInPlace(): self
    {
        $this->deleteIndex();
        $this->client->collections->create($this->schema->definition($this->name));
        $this->forgetCachedState();

        $this->searchables()->lazy()->each(fn ($searchables) => $this->insertMultiple($searchables));

        return $this;
    }

    private function importAllSearchablesInto(string $collection): void
    {
        // A queued rebuild could swap the alias before its indexing jobs finish.
        // Full alias rebuilds are synchronous; incremental writes still use the configured queue.
        $this->searchables()->lazy()->each(function ($references) use ($collection) {
            $references
                ->chunk(config('statamic.search.chunk_size'))
                ->each(function ($references) use ($collection) {
                    $this->importDocuments($collection, $this->documentsForReferences($references));
                });
        });
    }

    private function documentsForReferences($references): Documents
    {
        $providers = app(Providers::class);

        $documents = collect($references)
            ->groupBy(fn ($reference) => explode('::', $reference)[0])
            ->flatMap(function ($references, $prefix) use ($providers) {
                return $providers
                    ->getByPrefix($prefix)
                    ->find($references->map(fn ($reference) => Str::after($reference, '::'))->all())
                    ->all();
            })
            ->mapWithKeys(function (Searchable $item) {
                return [$item->getSearchReference() => $this->fields($item)];
            });

        return new Documents($documents);
    }

    private function importDocuments(string $collection, Documents $documents): void
    {
        if ($documents->isEmpty()) {
            return;
        }

        $sortField = Arr::get($this->config, 'settings.schema.default_sorting_field');
        $sortField = is_string($sortField) ? str_replace('->', '.', $sortField) : null;

        $documents = $documents
            ->map(function (array $document, string $reference) use ($sortField) {
                $document = array_merge($document, [
                    'id' => $this->documentId($reference),
                    'reference' => $reference,
                ]);

                if ($sortField && ! array_key_exists($sortField, $document)) {
                    $document[$sortField] = 0;
                }

                return $document;
            })
            ->values()
            ->all();

        $options = array_merge(
            ['action' => 'upsert', 'dirty_values' => 'coerce_or_drop'],
            Arr::get($this->config, 'settings.import_parameters', []),
        );

        try {
            $responses = $this->client->collections[$collection]->documents->import($documents, $options);
        } catch (HttpClientException|PsrClientException $e) {
            throw $this->connectionException($e);
        }

        // Imports can partially succeed even when another document is rejected.
        $this->forgetCachedState();

        $failures = collect($responses)
            ->filter(fn (array $response) => ($response['success'] ?? false) !== true);

        if ($failures->isNotEmpty()) {
            $message = $failures
                ->map(fn (array $failure) => $failure['error'] ?? 'Unknown Typesense import error.')
                ->unique()
                ->implode(' ');

            throw new \RuntimeException("Unable to import documents into Typesense index [{$this->name}]. {$message}");
        }

    }

    private function metadata(Searchable $searchable): array
    {
        $reference = $searchable->getSearchReference();

        return [
            'reference' => $reference,
            'type' => Str::before($reference, '::'),
            'status' => method_exists($searchable, 'status') ? $searchable->status() : 'published',
            'site' => $this->firstMethodValue($searchable, ['locale', 'site']),
            'collection' => $this->firstMethodValue($searchable, ['collectionHandle', 'collection']),
            'taxonomy' => $this->firstMethodValue($searchable, ['taxonomyHandle', 'taxonomy']),
            'container' => $this->firstMethodValue($searchable, ['containerHandle', 'container']),
            'date' => $this->firstMethodValue($searchable, ['date']),
        ];
    }

    private function firstMethodValue(Searchable $searchable, array $methods): mixed
    {
        foreach ($methods as $method) {
            if (method_exists($searchable, $method)) {
                return $searchable->{$method}();
            }
        }

        return null;
    }

    private function activeCollectionName(): ?string
    {
        try {
            return $this->client->aliases[$this->name]->retrieve()['collection_name'] ?? null;
        } catch (ObjectNotFound) {
            try {
                $this->client->collections[$this->name]->retrieve();

                return $this->name;
            } catch (ObjectNotFound) {
                return null;
            } catch (HttpClientException|PsrClientException $e) {
                throw $this->connectionException($e);
            }
        } catch (HttpClientException|PsrClientException $e) {
            throw $this->connectionException($e);
        }
    }

    private function collection()
    {
        return $this->client->collections[$this->name];
    }

    private function newCollectionName(): string
    {
        return sprintf(
            '%s_%s_%s',
            $this->name,
            now()->format('YmdHis'),
            Str::lower(Str::random(6)),
        );
    }

    private function forgetCachedState(): void
    {
        Blink::forget($this->existsBlinkKey());
        $this->schema->forget();
        Blink::put($this->searchVersionKey(), Blink::get($this->searchVersionKey(), 0) + 1);
    }

    private function searchVersionKey(): string
    {
        return 'search-typesense-version-'.$this->cacheFingerprint().'-'.$this->name;
    }

    private function existsBlinkKey(): string
    {
        return 'search-typesense-exists-'.$this->cacheFingerprint().'-'.$this->name;
    }

    private function handleTypesenseException(TypesenseClientError $e): never
    {
        $message = $e->getMessage();

        if (
            $e instanceof ObjectNotFound
            || Str::contains($message, ['Could not find a collection', 'Collection not found'])
        ) {
            throw new IndexNotFoundException("Index [{$this->name}] does not exist.", previous: $e);
        }

        if (preg_match("~field named [`'\"]?([^`'\"\\s]+)~i", $message, $matches)) {
            throw new \Exception(
                "Field [{$matches[1]}] does not exist in this Typesense index.",
                previous: $e,
            );
        }

        throw $e;
    }

    private function connectionException(Throwable $e): \RuntimeException
    {
        return new \RuntimeException(
            'Error connecting to Typesense. Check your API credentials and node configuration.',
            previous: $e,
        );
    }

    private function appendCommaSeparated(string|array|null $existing, string $value): string
    {
        $fields = is_array($existing) ? $existing : explode(',', (string) $existing);

        return collect($fields)
            ->push($value)
            ->map(fn (string $field) => str_replace('->', '.', trim($field)))
            ->filter()
            ->unique()
            ->implode(',');
    }

    private function removeCommaSeparated(string|array|null $existing, string $value): string
    {
        $fields = is_array($existing) ? $existing : explode(',', (string) $existing);

        return collect($fields)
            ->map(fn (string $field) => str_replace('->', '.', trim($field)))
            ->reject(fn (string $field) => $field === $value)
            ->filter()
            ->unique()
            ->implode(',');
    }

    private function embeddingFieldName(): string
    {
        return str_replace(
            '->',
            '.',
            (string) Arr::get($this->config, 'settings.embedding.name', 'embedding'),
        );
    }

    private function escapeFilterValue(string $value): string
    {
        return '`'.str_replace(['\\', '`'], ['\\\\', '\\`'], $value).'`';
    }
}
