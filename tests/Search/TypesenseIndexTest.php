<?php

namespace Tests\Search;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Search\Documents;
use Statamic\Search\IndexNotFoundException;
use Statamic\Search\Result;
use Statamic\Search\Typesense\Index;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\Search\Typesense\MakesTypesenseIndexes;
use Tests\TestCase;
use Typesense\Exceptions\TypesenseClientError;

class TypesenseIndexTest extends TestCase
{
    use IndexTests;
    use MakesTypesenseIndexes;
    use PreventSavingStacheItemsToDisk;

    public function getIndexClass()
    {
        return Index::class;
    }

    public function getIndex($name, $config, $locale)
    {
        return $this->makeTypesenseIndex($config, $locale, $name)[0];
    }

    #[Test]
    public function it_reindexes_by_atomically_swapping_an_alias()
    {
        [$index, $http] = $this->makeTypesenseIndex(name: 'articles');

        $http
            ->respond(['name' => 'articles', 'collection_name' => 'articles_old'])
            ->respond(['name' => 'articles_old', 'synonym_sets' => ['dashboard'], 'curation_sets' => ['featured']])
            ->respond(['name' => 'articles_new'])
            ->respond(['name' => 'articles', 'collection_name' => 'articles_new'])
            ->respond([]);

        $index->update();

        $this->assertCount(5, $http->requests);
        $this->assertSame('GET', $http->request(0)['method']);
        $this->assertStringContainsString('/aliases/articles', $http->request(0)['uri']);
        $this->assertSame('POST', $http->request(2)['method']);
        $this->assertStringEndsWith('/collections', $http->request(2)['uri']);

        $candidate = $http->json(2)['name'];
        $this->assertSame(['dashboard'], $http->json(2)['synonym_sets']);
        $this->assertSame(['featured'], $http->json(2)['curation_sets']);
        $this->assertStringStartsWith('articles_', $candidate);
        $this->assertSame($candidate, $http->json(3)['collection_name']);
        $this->assertStringContainsString('/collections/articles_old', $http->request(4)['uri']);
    }

    #[Test]
    public function it_migrates_a_legacy_plain_collection_to_an_alias()
    {
        [$index, $http] = $this->makeTypesenseIndex(name: 'articles');

        $http
            ->respond(['message' => 'Alias not found'], 404)
            ->respond(['name' => 'articles'])
            ->respond(['name' => 'articles'])
            ->respond(['name' => 'articles_new'])
            ->respond([])
            ->respond(['name' => 'articles']);

        $index->update();

        $this->assertCount(6, $http->requests);
        $this->assertSame('DELETE', $http->request(4)['method']);
        $this->assertStringContainsString('/collections/articles', $http->request(4)['uri']);
        $this->assertSame('PUT', $http->request(5)['method']);
        $this->assertStringContainsString('/aliases/articles', $http->request(5)['uri']);
    }

    #[Test]
    public function it_does_not_delete_a_candidate_when_a_failed_alias_response_was_applied()
    {
        [$index, $http] = $this->makeTypesenseIndex(name: 'articles');
        Carbon::setTestNow('2026-10-23 10:00:00');
        Str::createRandomStringsUsing(fn () => 'ABCDEF');

        $http
            ->respond(['name' => 'articles', 'collection_name' => 'articles_old'])
            ->respond(['name' => 'articles_old', 'synonym_sets' => ['dashboard'], 'curation_sets' => ['featured']])
            ->respond(['name' => 'articles_new'])
            ->respond(['message' => 'Upstream timed out'], 500)
            ->respond([
                'name' => 'articles',
                'collection_name' => 'articles_20261023100000_abcdef',
            ]);

        try {
            $index->update();
            $this->fail('The failed alias response should be surfaced.');
        } catch (TypesenseClientError) {
            //
        } finally {
            Str::createRandomStringsNormally();
            Carbon::setTestNow();
        }

        $this->assertCount(5, $http->requests);
        $this->assertSame('GET', $http->request(4)['method']);
        $this->assertStringContainsString('/aliases/articles', $http->request(4)['uri']);
    }

    #[Test]
    public function configured_sets_override_dashboard_sets_during_a_rebuild()
    {
        [$index, $http] = $this->makeTypesenseIndex([
            'settings' => ['schema' => ['synonym_sets' => [], 'curation_sets' => ['configured']]],
        ]);
        $http
            ->respond(['collection_name' => 'test_old'])
            ->respond(['synonym_sets' => ['dashboard'], 'curation_sets' => ['featured']])
            ->respond([])
            ->respond([])
            ->respond([]);

        $index->update();

        $this->assertSame([], $http->json(2)['synonym_sets']);
        $this->assertSame(['configured'], $http->json(2)['curation_sets']);
    }

    #[Test]
    public function a_failed_collection_creation_does_not_delete_an_existing_collection()
    {
        [$index, $http] = $this->makeTypesenseIndex();
        $http
            ->respond(['collection_name' => 'test_old'])
            ->respond(['name' => 'test_old'])
            ->respond(['message' => 'A collection with this name already exists'], 409);

        try {
            $index->update();
            $this->fail('The collection conflict should be surfaced.');
        } catch (TypesenseClientError) {
            $this->assertCount(3, $http->requests);
        }
    }

    #[Test]
    public function a_failed_import_keeps_the_existing_alias_and_removes_the_candidate()
    {
        \Statamic\Facades\Collection::make('blog')->save();
        EntryFactory::collection('blog')->id('one')->data(['title' => 'One'])->create();
        [$index, $http] = $this->makeTypesenseIndex(['searchables' => 'collection:blog']);
        $http
            ->respond(['collection_name' => 'test_old'])
            ->respond(['name' => 'test_old'])
            ->respond([])
            ->respond('{"success":false,"error":"Invalid document"}')
            ->respond(['collection_name' => 'test_old'])
            ->respond([]);

        try {
            $index->update();
            $this->fail('The import failure should be surfaced.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Invalid document', $e->getMessage());
        }

        $candidate = $http->json(2)['name'];
        $this->assertStringContainsString('/collections/'.$candidate.'/documents/import', $http->request(3)['uri']);
        $this->assertSame('entry::one', $http->json(3)['reference']);
        $this->assertSame('DELETE', $http->request(5)['method']);
        $this->assertStringEndsWith('/collections/'.$candidate, $http->request(5)['uri']);
        $this->assertNotContains('PUT', array_column($http->requests, 'method'));
    }

    #[Test]
    public function it_can_reindex_in_place_when_aliases_are_not_wanted()
    {
        [$index, $http] = $this->makeTypesenseIndex([
            'settings' => ['reindex' => 'in_place'],
        ]);

        $http
            ->respond(['message' => 'Alias not found'], 404)
            ->respond(['message' => 'Collection not found'], 404)
            ->respond(['name' => 'test']);

        $index->update();

        $this->assertCount(3, $http->requests);
        $this->assertSame('test', $http->json(2)['name']);
    }

    #[Test]
    public function it_only_checks_existence_once_per_request()
    {
        [$index, $http] = $this->makeTypesenseIndex(name: 'existence');

        $http
            ->respond(['message' => 'Alias not found'], 404)
            ->respond(['name' => 'existence']);

        $this->assertTrue($index->exists());
        $this->assertTrue($index->exists());
        $this->assertCount(2, $http->requests);
    }

    #[Test]
    public function it_imports_documents_using_url_safe_ids_and_checks_each_response()
    {
        [$index, $http] = $this->makeTypesenseIndex();
        $http->respond('{"success":true}');

        $index->insertDocuments(new Documents([
            'asset::images::photos/cat.jpg' => ['title' => 'Cat'],
        ]));

        $document = json_decode($http->request(0)['body'], true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame(
            hash('sha256', 'asset::images::photos/cat.jpg'),
            $document['id'],
        );
        $this->assertSame('asset::images::photos/cat.jpg', $document['reference']);
        $this->assertStringContainsString('action=upsert', $http->request(0)['uri']);
        $this->assertStringContainsString('dirty_values=coerce_or_drop', $http->request(0)['uri']);
    }

    #[Test]
    public function it_adds_typed_statamic_metadata_to_documents()
    {
        [$index] = $this->makeTypesenseIndex();
        $entry = EntryFactory::id('one')
            ->collection('blog')
            ->data(['title' => 'One'])
            ->make();

        $this->assertSame([
            'title' => 'One',
            'reference' => 'entry::one',
            'type' => 'entry',
            'status' => 'published',
            'site' => 'en',
            'collection' => 'blog',
        ], $index->fields($entry));
    }

    #[Test]
    public function it_surfaces_individual_import_failures()
    {
        [$index, $http] = $this->makeTypesenseIndex();
        $http->respond('{"success":false,"error":"Field `date` must be an int64."}');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Field `date` must be an int64.');

        $index->insertDocuments(new Documents([
            'entry::one' => ['title' => 'One'],
        ]));
    }

    #[Test]
    public function deleting_an_already_missing_document_is_a_noop()
    {
        [$index, $http] = $this->makeTypesenseIndex();
        $http->respond(['message' => 'Document not found'], 404);

        $document = Mockery::mock(\Statamic\Contracts\Search\Searchable::class);
        $document->shouldReceive('getSearchReference')->andReturn('entry::one');

        $index->delete($document);

        $this->assertCount(1, $http->requests);
    }

    #[Test]
    public function it_generates_a_scoped_frontend_key_with_published_site_constraints()
    {
        Carbon::setTestNow('2026-10-23 12:00:00');
        [$index, , $client] = $this->makeTypesenseIndex([
            'settings' => [
                'search_parameters' => [
                    'filter_by' => 'collection:=blog',
                ],
            ],
        ], locale: 'en');

        $parameters = [
            'filter_by' => 'status:=published && site:=`en` && (collection:=blog) && (type:=entry)',
            'expires_at' => now()->addMinute()->timestamp,
        ];

        $this->assertSame(
            $client->keys->generateScopedSearchKey('search-key', $parameters),
            $index->scopedSearchKey(['filter_by' => 'type:=entry'], 60),
        );
    }

    #[Test]
    public function a_single_site_scoped_key_does_not_filter_an_unlocalized_index_by_site()
    {
        Carbon::setTestNow('2026-10-23 12:00:00');
        [$index, , $client] = $this->makeTypesenseIndex();

        $parameters = [
            'filter_by' => 'status:=published',
            'expires_at' => now()->addHour()->timestamp,
        ];

        $this->assertSame(
            $client->keys->generateScopedSearchKey('search-key', $parameters),
            $index->scopedSearchKey(),
        );
    }

    #[Test]
    public function documents_missing_the_default_sorting_field_still_import()
    {
        [$index, $http] = $this->makeTypesenseIndex([
            'settings' => [
                'schema' => ['default_sorting_field' => 'date'],
            ],
        ]);
        $http->respond('{"success":true}');

        $index->insertDocuments(new Documents([
            'entry::one' => ['title' => 'One'],
        ]));

        $this->assertSame(0, $http->json(0)['date']);
    }

    #[Test]
    public function it_creates_and_caches_a_natural_language_model()
    {
        [$index, $http] = $this->makeTypesenseIndex([
            'settings' => [
                'natural_language' => [
                    'model' => 'statamic_articles',
                    'model_config' => [
                        'model_name' => 'openai/gpt-4.1-mini',
                        'api_key' => 'llm-key',
                    ],
                ],
            ],
        ]);

        $http
            ->respond(['message' => 'Model not found'], 404)
            ->respond(['id' => 'statamic_articles']);

        $this->assertSame('statamic_articles', $index->ensureNaturalLanguageModel());
        $this->assertSame('statamic_articles', $index->ensureNaturalLanguageModel());
        $this->assertCount(2, $http->requests);
        $this->assertSame([
            'model_name' => 'openai/gpt-4.1-mini',
            'api_key' => 'llm-key',
            'id' => 'statamic_articles',
        ], $http->json(1));
    }

    #[Test]
    public function it_exposes_all_highlights_snippets_and_text_match_data()
    {
        [$index] = $this->makeTypesenseIndex();
        $result = Mockery::mock(Result::class);
        $result->shouldReceive('getRawResult')->andReturn([
            'search_text_match' => 123,
            'search_highlights' => [
                ['field' => 'title', 'snippet' => '<mark>Red</mark> shirt'],
                'content' => ['snippet' => 'A <mark>shirt</mark>'],
            ],
        ]);

        $this->assertSame([
            'search_highlights' => [
                'title' => ['field' => 'title', 'snippet' => '<mark>Red</mark> shirt'],
                'content' => ['snippet' => 'A <mark>shirt</mark>'],
            ],
            'search_snippets' => [
                'title' => '<mark>Red</mark> shirt',
                'content' => 'A <mark>shirt</mark>',
            ],
            'search_text_match' => 123,
        ], $index->extraAugmentedResultData($result));
    }

    #[Test]
    public function it_flattens_grouped_hits_from_dashboard_presets()
    {
        [$index, $http] = $this->makeTypesenseIndex();
        $http->respond([
            'results' => [
                [
                    'found' => 2,
                    'grouped_hits' => [
                        [
                            'group_key' => ['USA'],
                            'hits' => [
                                [
                                    'document' => ['reference' => 'entry::one', 'title' => 'One'],
                                    'text_match' => 90,
                                ],
                            ],
                        ],
                        [
                            'group_key' => ['UK'],
                            'hits' => [
                                [
                                    'document' => ['reference' => 'entry::two', 'title' => 'Two'],
                                    'text_match' => 80,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response = $index->searchUsingApi('red');

        $this->assertSame(2, $response['found']);
        $this->assertSame(['entry::one', 'entry::two'], $response['hits']->pluck('reference')->all());
        $this->assertSame([2, 1], $response['hits']->pluck('search_score')->all());
    }

    #[Test]
    public function it_maps_missing_collection_errors_to_statamics_exception()
    {
        [$index, $http] = $this->makeTypesenseIndex();
        $http->respond([
            'results' => [
                [
                    'code' => 404,
                    'error' => 'Could not find a collection named `test`.',
                ],
            ],
        ]);

        $this->expectException(IndexNotFoundException::class);
        $this->expectExceptionMessage('Index [test] does not exist.');

        $index->searchUsingApi('foo');
    }

    #[Test]
    public function it_maps_unknown_search_fields_to_a_helpful_exception()
    {
        [$index, $http] = $this->makeTypesenseIndex();
        $http->respond([
            'results' => [
                [
                    'code' => 400,
                    'error' => 'Could not find a field named `subtitle` in the schema.',
                ],
            ],
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Field [subtitle] does not exist in this Typesense index.');

        $index->searchUsingApi('foo');
    }
}
