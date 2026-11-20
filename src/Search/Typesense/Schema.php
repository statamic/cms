<?php

namespace Statamic\Search\Typesense;

use Http\Client\Exception as HttpClientException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Psr\Http\Client\ClientExceptionInterface as PsrClientException;
use Statamic\Facades\Blink;
use Statamic\Search\IndexNotFoundException;
use Typesense\Client;
use Typesense\Exceptions\ObjectNotFound;

class Schema
{
    public function __construct(
        private Client $client,
        private string $collection,
        private array $config,
    ) {
    }

    public function definition(string $name): array
    {
        $schema = Arr::get($this->config, 'settings.schema', []);
        $overrides = collect(Arr::pull($schema, 'fields', []))
            ->map(function (array $field) {
                $field['name'] = $this->normalizeFieldName($field['name']);

                return $field;
            })
            ->keyBy('name');

        $fields = $this->defaultFields()
            ->map(function (array $field) use ($overrides) {
                return array_merge($field, $overrides->get($field['name'], []));
            })
            ->keyBy('name');

        $overrides->each(function (array $field, string $name) use ($fields) {
            if (! $fields->has($name)) {
                $fields->put($name, $field);
            }
        });

        $definition = array_merge(
            [
                'name' => $name,
                'enable_nested_fields' => true,
            ],
            $schema,
            [
                'name' => $name,
                'fields' => $fields->values()->all(),
            ],
        );

        if ($sortField = $definition['default_sorting_field'] ?? null) {
            $sortField = $this->normalizeFieldName($sortField);
            $definition['default_sorting_field'] = $sortField;
            $definition['fields'] = collect($definition['fields'])
                ->map(function (array $field) use ($sortField) {
                    if ($field['name'] !== $sortField) {
                        return $field;
                    }

                    $field['optional'] = false;
                    $field['sort'] = true;

                    return $field;
                })
                ->all();
        }

        return $definition;
    }

    public function queryBy(): string
    {
        $queryBy = Arr::get($this->config, 'settings.search_parameters.query_by')
            ?? Arr::get($this->config, 'settings.search_options.query_by');

        if ($queryBy) {
            $queryBy = is_array($queryBy) ? $queryBy : explode(',', $queryBy);

            if ($embedding = $this->embeddingField()) {
                $queryBy[] = $embedding['name'];
            }

            return collect($queryBy)
                ->map(fn (string $field) => $this->normalizeFieldName(trim($field)))
                ->filter()
                ->unique()
                ->implode(',');
        }

        $searchableTypes = ['string', 'string[]'];
        $fields = $this->fields();

        $queryBy = collect($this->config['fields'] ?? [])
            ->filter(fn ($field) => is_string($field))
            ->map(fn (string $field) => $this->normalizeFieldName($field))
            ->filter(function (string $field) use ($fields, $searchableTypes) {
                $definition = $fields->get($field);

                return $definition
                    && ($definition['index'] ?? true) !== false
                    && in_array($definition['type'] ?? null, $searchableTypes, true);
            });

        if ($embedding = $this->embeddingField()) {
            $queryBy->push($embedding['name']);
        }

        return $queryBy->unique()->implode(',') ?: 'reference';
    }

    public function fields(): Collection
    {
        return Blink::once($this->blinkKey(), function () {
            try {
                $schema = $this->client->collections[$this->collection]->retrieve();
            } catch (ObjectNotFound $e) {
                throw new IndexNotFoundException(
                    "Index [{$this->collection}] does not exist.",
                    previous: $e,
                );
            } catch (HttpClientException|PsrClientException $e) {
                throw new \RuntimeException(
                    'Error connecting to Typesense. Check your API credentials and node configuration.',
                    previous: $e,
                );
            }

            return collect($schema['fields'] ?? [])->keyBy('name');
        });
    }

    public function field(string $name): ?array
    {
        $fields = $this->fields();
        $name = $this->normalizeFieldName($name);

        return $fields->get($name);
    }

    public function filterable(string $name): bool
    {
        $field = $this->field($name);

        return $field && ($field['index'] ?? true) !== false;
    }

    public function sortable(string $name): bool
    {
        return ($this->field($name)['sort'] ?? false) === true;
    }

    public function forget(): void
    {
        Blink::forget($this->blinkKey());
    }

    private function defaultFields(): Collection
    {
        $embeddingSources = $this->embeddingField()['embed']['from'] ?? [];

        $fields = collect($this->config['fields'] ?? [])
            ->filter(fn ($field) => is_string($field) && $field !== 'id')
            ->map(fn (string $name) => [
                'name' => $this->normalizeFieldName($name),
                'type' => in_array($this->normalizeFieldName($name), $embeddingSources, true) ? 'string' : 'auto',
                'optional' => true,
            ]);

        $fields->push(
            ['name' => 'reference', 'type' => 'string'],
            ['name' => 'type', 'type' => 'string', 'facet' => true],
            ['name' => 'status', 'type' => 'string', 'facet' => true, 'optional' => true],
            ['name' => 'site', 'type' => 'string', 'facet' => true, 'optional' => true],
            ['name' => 'collection', 'type' => 'string', 'facet' => true, 'optional' => true],
            ['name' => 'taxonomy', 'type' => 'string', 'facet' => true, 'optional' => true],
            ['name' => 'container', 'type' => 'string', 'facet' => true, 'optional' => true],
            ['name' => 'date', 'type' => 'int64', 'sort' => true, 'optional' => true],
        );

        if ($embedding = $this->embeddingField()) {
            $fields->push($embedding);
        }

        $fields->push([
            'name' => '.*',
            'type' => 'auto',
            'optional' => true,
        ]);

        return $fields->keyBy('name')->values();
    }

    private function embeddingField(): ?array
    {
        if (! $embedding = Arr::get($this->config, 'settings.embedding')) {
            return null;
        }

        $name = $this->normalizeFieldName(Arr::pull($embedding, 'name', 'embedding'));
        Arr::forget($embedding, 'alpha');

        if (is_array($embedding['from'] ?? null)) {
            $embedding['from'] = array_map(
                fn (string $field) => $this->normalizeFieldName($field),
                $embedding['from'],
            );
        }

        return [
            'name' => $name,
            'type' => 'float[]',
            'embed' => $embedding,
        ];
    }

    private function normalizeFieldName(string $name): string
    {
        return str_replace('->', '.', $name);
    }

    private function blinkKey(): string
    {
        $connection = [
            Arr::get($this->config, 'client.nodes', $this->config['nodes'] ?? []),
            Arr::get($this->config, 'client.api_key', Arr::get($this->config, 'credentials.api_key')),
        ];

        return 'search-typesense-schema-'.hash('sha256', serialize($connection)).'-'.$this->collection;
    }
}
