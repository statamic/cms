<?php

namespace Tests\Search;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Search\Typesense\Normalizer;
use Tests\Search\Typesense\MakesTypesenseIndexes;
use Tests\TestCase;

class TypesenseSchemaTest extends TestCase
{
    use MakesTypesenseIndexes;

    #[Test]
    public function it_derives_a_typed_schema_and_merges_field_overrides()
    {
        [$index] = $this->makeTypesenseIndex([
            'fields' => ['title', 'tags', 'date', 'author->name', 'price', 'categories'],
            'settings' => [
                'schema' => [
                    'default_sorting_field' => 'date',
                    'synonym_sets' => ['blog'],
                    'curation_sets' => ['featured'],
                    'fields' => [
                        ['name' => 'tags', 'type' => 'string[]', 'facet' => true],
                        ['name' => 'author->name', 'type' => 'string', 'facet' => true],
                        ['name' => 'popularity', 'type' => 'int32', 'sort' => true],
                    ],
                ],
                'embedding' => [
                    'name' => 'vector',
                    'from' => ['title', 'author->name'],
                    'model_config' => ['model_name' => 'ts/all-MiniLM-L12-v2'],
                    'alpha' => 0.4,
                ],
            ],
        ]);

        $schema = $index->schema()->definition('articles_123');
        $fields = collect($schema['fields'])->keyBy('name');

        $this->assertSame('articles_123', $schema['name']);
        $this->assertTrue($schema['enable_nested_fields']);
        $this->assertSame('date', $schema['default_sorting_field']);
        $this->assertSame(['blog'], $schema['synonym_sets']);
        $this->assertSame(['featured'], $schema['curation_sets']);
        $this->assertSame('string', $fields['title']['type']);
        $this->assertSame('auto', $fields['price']['type']);
        $this->assertSame('auto', $fields['categories']['type']);
        $this->assertSame('string[]', $fields['tags']['type']);
        $this->assertTrue($fields['tags']['facet']);
        $this->assertTrue($fields['author.name']['facet']);
        $this->assertSame('int64', $fields['date']['type']);
        $this->assertTrue($fields['date']['sort']);
        $this->assertFalse($fields['date']['optional']);
        $this->assertSame('int32', $fields['popularity']['type']);
        $this->assertSame([
            'from' => ['title', 'author.name'],
            'model_config' => ['model_name' => 'ts/all-MiniLM-L12-v2'],
        ], $fields['vector']['embed']);
        $this->assertSame('auto', $fields['.*']['type']);
    }

    #[Test]
    public function it_builds_query_by_from_only_searchable_live_fields()
    {
        [$index, $http] = $this->makeTypesenseIndex([
            'fields' => ['title', 'summary', 'price', 'tags', 'author->name'],
            'settings' => [
                'search_parameters' => ['query_by' => null],
                'embedding' => [
                    'from' => ['title'],
                    'model_config' => ['model_name' => 'ts/all-MiniLM-L12-v2'],
                ],
            ],
        ]);

        $http->respond([
            'fields' => [
                ['name' => 'title', 'type' => 'string'],
                ['name' => 'summary', 'type' => 'string', 'index' => false],
                ['name' => 'price', 'type' => 'float'],
                ['name' => 'tags', 'type' => 'string[]'],
                ['name' => 'author.name', 'type' => 'string'],
                ['name' => 'embedding', 'type' => 'float[]'],
            ],
        ]);

        $this->assertSame('title,tags,author.name,embedding', $index->schema()->queryBy());
        $this->assertSame('title,tags,author.name,embedding', $index->schema()->queryBy());
        $this->assertCount(1, $http->requests);
    }

    #[Test]
    public function it_uses_the_internal_reference_when_no_configured_fields_are_searchable()
    {
        [$index, $http] = $this->makeTypesenseIndex([
            'fields' => [],
            'settings' => [
                'search_parameters' => ['query_by' => null],
            ],
        ]);
        $http->respond([
            'fields' => [
                ['name' => 'reference', 'type' => 'string'],
            ],
        ]);

        $this->assertSame('reference', $index->schema()->queryBy());
    }

    #[Test]
    public function it_normalizes_dates_collections_handles_and_stringables()
    {
        $handle = new class
        {
            public function handle()
            {
                return 'pages';
            }
        };

        $stringable = new class implements \Stringable
        {
            public function __toString(): string
            {
                return 'string value';
            }
        };

        $normalizer = new Normalizer;

        $this->assertSame([
            'date' => 1761213600,
            'items' => ['one', 'two'],
            'author.name' => 'Jack',
            'handle' => 'pages',
            'stringable' => 'string value',
        ], $normalizer->document([
            'date' => Carbon::parse('2025-10-23 10:00:00', 'UTC'),
            'items' => collect(['one', 'two']),
            'author->name' => 'Jack',
            'handle' => $handle,
            'stringable' => $stringable,
            'unsupported' => new \stdClass,
        ]));
    }
}
