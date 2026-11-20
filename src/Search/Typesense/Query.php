<?php

namespace Statamic\Search\Typesense;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades\Blink;
use Statamic\Search\QueryBuilder;

class Query extends QueryBuilder
{
    private array $generatedNaturalLanguageParams = [];

    public function __construct(Index $index)
    {
        parent::__construct($index);
    }

    public function get($columns = ['*'])
    {
        if (! $this->canPushDown()) {
            return parent::get($columns);
        }

        return $this->withFakeQueryLogging(function () {
            $response = $this->execute(array_merge(
                $this->translatedParameters(),
                [
                    'offset' => $this->offset,
                    'limit' => $this->requestedLimit(),
                ],
            ));

            return $this->transformResults($response['hits']);
        });
    }

    public function count()
    {
        if (! $this->canPushDown()) {
            return parent::count();
        }

        $response = $this->execute(array_merge(
            $this->translatedParameters(),
            ['offset' => 0, 'limit' => 1],
        ));

        $count = max(0, $response['found'] - $this->offset);

        return is_null($this->limit) ? $count : min($count, $this->limit);
    }

    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        if (! $this->canPushDown()) {
            return parent::paginate($perPage, $columns, $pageName, $page);
        }

        $page = $page ?: Paginator::resolveCurrentPage($pageName);
        $perPage = max(
            1,
            min(
                (int) ($perPage ?: $this->defaultPerPageSize()),
                $this->maxResults(),
            ),
        );

        $response = $this->withFakeQueryLogging(fn () => $this->execute(array_merge(
            $this->translatedParameters(),
            [
                'offset' => max(0, ($page - 1) * $perPage),
                'limit' => $perPage,
            ],
        )));

        return $this->paginator(
            $this->transformResults($response['hits']),
            $response['found'],
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ],
        );
    }

    public function getSearchResults($query)
    {
        return $this->execute([
            'offset' => 0,
            'limit' => $this->maxResults(),
        ])['hits'];
    }

    public function naturalLanguageParams(): array
    {
        return $this->generatedNaturalLanguageParams;
    }

    public function rawWheres(): array
    {
        return $this->wheres;
    }

    protected function getCountForPagination()
    {
        if (! $this->canPushDown()) {
            return parent::getCountForPagination();
        }

        return $this->execute(array_merge(
            $this->translatedParameters(),
            ['offset' => 0, 'limit' => 1],
        ))['found'];
    }

    private function execute(array $parameters): array
    {
        [$query, $parameters] = $this->naturalLanguageParameters($parameters);
        $cacheKey = 'search-typesense-'.$this->index->searchCacheFingerprint().'-'.$this->index->name().'-'.hash('sha256', serialize([
            $query,
            $parameters,
        ]));

        $response = Blink::once(
            $cacheKey,
            fn () => $this->index->searchUsingApi($query, $parameters),
        );

        $this->rememberNaturalLanguageParams($response['raw']);

        return $response;
    }

    private function canPushDown(): bool
    {
        if ($this->randomize) {
            return false;
        }

        $translator = $this->translator();

        return ! is_null($translator->filters($this->wheres))
            && ! is_null($translator->sorts($this->orderBys));
    }

    private function translatedParameters(): array
    {
        $translator = $this->translator();
        $parameters = [];

        if ($filter = $translator->filters($this->wheres)) {
            $parameters['filter_by'] = $filter;
        }

        if ($sort = $translator->sorts($this->orderBys)) {
            $parameters['sort_by'] = $sort;
        }

        return $parameters;
    }

    private function translator(): FilterTranslator
    {
        return new FilterTranslator($this->index->schema());
    }

    private function requestedLimit(): int
    {
        return min((int) ($this->limit ?? $this->maxResults()), $this->maxResults());
    }

    private function maxResults(): int
    {
        return max(1, min(
            250,
            (int) Arr::get($this->index->config(), 'settings.max_results', 250),
        ));
    }

    private function naturalLanguageParameters(array $parameters): array
    {
        if (! $this->naturalLanguage) {
            return [$this->query, $parameters];
        }

        $model = $this->index->ensureNaturalLanguageModel();

        if (! $model) {
            throw new \LogicException(
                "Natural Language Search has not been configured for Typesense index [{$this->index->name()}]."
            );
        }

        $cacheKey = $this->naturalLanguageCacheKey($model);
        $generated = Blink::once($cacheKey, fn () => Cache::get($cacheKey));

        if ($generated) {
            $this->generatedNaturalLanguageParams = $generated;

            return $this->applyGeneratedParameters($generated, $parameters);
        }

        $parameters['nl_query'] = true;
        $parameters['nl_model_id'] = $model;

        return [$this->query, $parameters];
    }

    private function applyGeneratedParameters(array $generated, array $parameters): array
    {
        $defaults = Arr::get(
            $this->index->config(),
            'settings.search_parameters',
            Arr::get($this->index->config(), 'settings.search_options', []),
        );

        if (array_key_exists('sort_by', $defaults) && ! array_key_exists('sort_by', $parameters)) {
            $parameters['sort_by'] = $defaults['sort_by'];
        }

        $query = Arr::pull($generated, 'q', $this->query);
        $generatedFilter = Arr::pull($generated, 'filter_by');
        $explicitFilter = $parameters['filter_by'] ?? null;
        $parameters = array_merge($generated, $parameters);

        if ($generatedFilter && $explicitFilter) {
            $parameters['filter_by'] = "({$explicitFilter}) && ({$generatedFilter})";
        } elseif ($generatedFilter) {
            $parameters['filter_by'] = $generatedFilter;
        }

        return [$query, $parameters];
    }

    private function rememberNaturalLanguageParams(array $response): void
    {
        if (! $this->naturalLanguage) {
            return;
        }

        $generated = Arr::get($response, 'parsed_nl_query.generated_params');

        if (! is_array($generated)) {
            return;
        }

        $augmentedQuery = Arr::get($response, 'parsed_nl_query.augmented_params.q');

        if (! is_null($augmentedQuery)) {
            $generated['q'] = $augmentedQuery;
        }

        $augmentedSort = Arr::get($response, 'parsed_nl_query.augmented_params.sort_by');

        if (! is_null($augmentedSort)) {
            $generated['sort_by'] = $augmentedSort;
        }

        if ($generated === []) {
            return;
        }

        $this->generatedNaturalLanguageParams = $generated;
        $model = Arr::get($this->index->config(), 'settings.natural_language.model')
            ?? 'statamic_'.$this->index->name();

        $cacheKey = $this->naturalLanguageCacheKey($model);
        Blink::put($cacheKey, $generated);
        Cache::put($cacheKey, $generated, now()->addMinutes(10));
    }

    private function naturalLanguageCacheKey(string $model): string
    {
        return 'statamic.search.typesense.nl-params.'.hash('sha256', serialize([
            $this->index->cacheFingerprint(),
            $this->index->name(),
            $model,
            $this->query,
            Arr::get($this->index->config(), 'fields'),
            Arr::get($this->index->config(), 'settings.schema'),
            Arr::get($this->index->config(), 'settings.search_parameters'),
            Arr::get($this->index->config(), 'settings.search_options'),
            Arr::get($this->index->config(), 'settings.natural_language'),
        ]));
    }
}
