<?php

namespace Tests\Search;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Search\Typesense\Query;
use Tests\Search\Typesense\MakesTypesenseIndexes;
use Tests\TestCase;

class TypesenseQueryTest extends TestCase
{
    use MakesTypesenseIndexes;

    #[Test]
    public function it_preserves_typesenses_ranking_and_exposes_match_data()
    {
        [$index, $http] = $this->makeTypesenseIndex();
        $http->respond($this->response([
            $this->hit('entry::one', 'One', 900),
            $this->hit('entry::two', 'Two', 800),
            $this->hit('entry::three', 'Three', 700),
        ], 3));

        $results = $index->search('red')->withoutData()->get();

        $this->assertSame(
            ['entry::one', 'entry::two', 'entry::three'],
            $results->map(fn ($result) => $result->get('reference'))->all(),
        );
        $this->assertSame([3, 2, 1], $results->map->getScore()->all());
        $this->assertSame(900, $results->first()->get('search_text_match'));

        $search = $http->json(0)['searches'][0];
        $this->assertSame('red', $search['q']);
        $this->assertSame('title', $search['query_by']);
        $this->assertSame(0, $search['offset']);
        $this->assertSame(250, $search['limit']);
    }

    #[Test]
    public function it_pushes_supported_filters_sorting_and_limits_to_typesense()
    {
        [$index, $http] = $this->makeTypesenseIndex();
        $http
            ->respond([
                'fields' => [
                    ['name' => 'status', 'type' => 'string'],
                    ['name' => 'date', 'type' => 'int64', 'sort' => true],
                ],
            ])
            ->respond($this->response([$this->hit('entry::one', 'One')], 1));

        $results = $index
            ->search('red')
            ->withoutData()
            ->whereStatus('published')
            ->orderBy('date', 'desc')
            ->offset(5)
            ->limit(20)
            ->get();

        $this->assertCount(1, $results);
        $search = $http->json(1)['searches'][0];
        $this->assertSame('status:=`published`', $search['filter_by']);
        $this->assertSame('date:desc', $search['sort_by']);
        $this->assertSame(5, $search['offset']);
        $this->assertSame(20, $search['limit']);
    }

    #[Test]
    public function it_falls_back_to_statamic_filtering_when_any_clause_cannot_be_translated()
    {
        [$index, $http] = $this->makeTypesenseIndex();
        $http
            ->respond([
                'fields' => [
                    ['name' => 'title', 'type' => 'string'],
                ],
            ])
            ->respond($this->response([
                $this->hit('entry::one', 'Red shirt'),
                $this->hit('entry::two', 'Blue jeans'),
                $this->hit('entry::three', 'Dark red hat'),
            ], 3));

        $results = $index
            ->search('clothes')
            ->withoutData()
            ->where('title', 'like', '%red%')
            ->get();

        $this->assertSame(
            ['entry::one', 'entry::three'],
            $results->map(fn ($result) => $result->get('reference'))->all(),
        );

        $search = $http->json(1)['searches'][0];
        $this->assertArrayNotHasKey('filter_by', $search);
        $this->assertSame(250, $search['limit']);
    }

    #[Test]
    public function pagination_uses_typesenses_found_count_and_server_side_window()
    {
        [$index, $http] = $this->makeTypesenseIndex();
        $http
            ->respond([
                'fields' => [
                    ['name' => 'status', 'type' => 'string'],
                ],
            ])
            ->respond($this->response([
                $this->hit('entry::twenty-one', 'Twenty one'),
                $this->hit('entry::twenty-two', 'Twenty two'),
            ], 42));

        $paginator = $index
            ->search('red')
            ->withoutData()
            ->whereStatus('published')
            ->paginate(10, page: 3);

        $this->assertSame(42, $paginator->total());
        $this->assertCount(2, $paginator);

        $search = $http->json(1)['searches'][0];
        $this->assertSame(20, $search['offset']);
        $this->assertSame(10, $search['limit']);
    }

    #[Test]
    public function pagination_is_capped_without_skipping_results()
    {
        [$index, $http] = $this->makeTypesenseIndex();
        $http->respond($this->response([], 1000));

        $paginator = $index
            ->search('red')
            ->withoutData()
            ->paginate(500, page: 2);

        $search = $http->json(0)['searches'][0];
        $this->assertSame(250, $paginator->perPage());
        $this->assertSame(250, $search['offset']);
        $this->assertSame(250, $search['limit']);
    }

    #[Test]
    public function count_uses_typesenses_found_count_and_honors_offset_and_limit()
    {
        [$index, $http] = $this->makeTypesenseIndex();
        $http->respond($this->response([], 42));

        $count = $index
            ->search('red')
            ->withoutData()
            ->offset(10)
            ->limit(5)
            ->count();

        $this->assertSame(5, $count);
        $this->assertSame(1, $http->json(0)['searches'][0]['limit']);
    }

    #[Test]
    public function it_configures_hybrid_search_and_excludes_embeddings_from_results()
    {
        [$index, $http] = $this->makeTypesenseIndex([
            'settings' => [
                'search_parameters' => [
                    'query_by' => ['title', 'author->name'],
                    'include_fields' => ['title'],
                    'exclude_fields' => ['reference', 'private->notes'],
                ],
                'embedding' => [
                    'from' => ['title'],
                    'model_config' => ['model_name' => 'ts/all-MiniLM-L12-v2'],
                    'alpha' => 0.4,
                ],
            ],
        ]);
        $http->respond($this->response([], 0));

        $index->search('red')->withoutData()->get();

        $search = $http->json(0)['searches'][0];
        $this->assertSame('title,author.name,embedding', $search['query_by']);
        $this->assertSame('title,reference', $search['include_fields']);
        $this->assertSame('private.notes,embedding', $search['exclude_fields']);
        $this->assertSame('embedding:([], alpha: 0.4)', $search['vector_query']);
    }

    #[Test]
    public function presets_can_supply_query_by_for_dashboard_managed_relevance()
    {
        [$index, $http] = $this->makeTypesenseIndex([
            'settings' => [
                'search_parameters' => [
                    'query_by' => null,
                    'preset' => 'articles_default',
                ],
            ],
        ]);
        $http->respond($this->response([], 0));

        $index->search('red')->withoutData()->get();

        $search = $http->json(0)['searches'][0];
        $this->assertSame('articles_default', $search['preset']);
        $this->assertArrayNotHasKey('query_by', $search);
    }

    #[Test]
    public function natural_language_search_reuses_generated_parameters_on_later_pages()
    {
        [$index, $http] = $this->makeTypesenseIndex([
            'settings' => [
                'natural_language' => [
                    'model' => 'statamic_articles',
                ],
            ],
        ]);

        $http
            ->respond([
                'results' => [
                    [
                        'found' => 12,
                        'hits' => [$this->hit('entry::one', 'Red shirt')],
                        'parsed_nl_query' => [
                            'generated_params' => [
                                'filter_by' => 'color:=`red` && price:<50',
                                'sort_by' => 'price:asc',
                            ],
                            'augmented_params' => [
                                'q' => '*',
                            ],
                        ],
                    ],
                ],
            ])
            ->respond($this->response([$this->hit('entry::six', 'Another red shirt')], 12));

        $first = (new Query($index))
            ->query('show me red shirts under 50')
            ->naturalLanguage()
            ->withoutData();
        $first->paginate(5, page: 1);

        $second = (new Query($index))
            ->query('show me red shirts under 50')
            ->naturalLanguage()
            ->withoutData();
        $second->paginate(5, page: 2);

        $firstSearch = $http->json(0)['searches'][0];
        $this->assertTrue($firstSearch['nl_query']);
        $this->assertSame('statamic_articles', $firstSearch['nl_model_id']);

        $secondSearch = $http->json(1)['searches'][0];
        $this->assertArrayNotHasKey('nl_query', $secondSearch);
        $this->assertSame('*', $secondSearch['q']);
        $this->assertSame('color:=`red` && price:<50', $secondSearch['filter_by']);
        $this->assertSame('price:asc', $secondSearch['sort_by']);
        $this->assertSame(5, $secondSearch['offset']);
        $this->assertSame([
            'filter_by' => 'color:=`red` && price:<50',
            'sort_by' => 'price:asc',
            'q' => '*',
        ], $second->naturalLanguageParams());
    }

    #[Test]
    public function searches_with_different_index_settings_do_not_share_cached_results()
    {
        [$first, $firstHttp] = $this->makeTypesenseIndex([
            'settings' => ['search_parameters' => ['filter_by' => 'collection:=blog']],
        ]);
        [$second, $secondHttp] = $this->makeTypesenseIndex([
            'settings' => ['search_parameters' => ['filter_by' => 'collection:=news']],
        ]);
        $firstHttp->respond($this->response([$this->hit('entry::blog', 'Blog')], 1));
        $secondHttp->respond($this->response([$this->hit('entry::news', 'News')], 1));

        $this->assertSame('Blog', $first->search('red')->withoutData()->get()->first()->get('title'));
        $this->assertSame('News', $second->search('red')->withoutData()->get()->first()->get('title'));
    }

    #[Test]
    public function importing_documents_invalidates_cached_search_results()
    {
        [$index, $http] = $this->makeTypesenseIndex();
        $http
            ->respond($this->response([$this->hit('entry::one', 'Old')], 1))
            ->respond('{"success":true}')
            ->respond($this->response([$this->hit('entry::one', 'New')], 1));

        $this->assertSame('Old', $index->search('red')->withoutData()->get()->first()->get('title'));
        $index->insertDocuments(new \Statamic\Search\Documents([
            'entry::one' => ['title' => 'New'],
        ]));
        $this->assertSame('New', $index->search('red')->withoutData()->get()->first()->get('title'));
    }

    #[Test]
    public function replayed_natural_language_sort_does_not_override_configured_sorting()
    {
        [$index, $http] = $this->makeTypesenseIndex([
            'settings' => [
                'search_parameters' => ['sort_by' => 'date:desc'],
                'natural_language' => ['model' => 'articles'],
            ],
        ]);
        $http
            ->respond(['results' => [[
                'found' => 12,
                'hits' => [],
                'parsed_nl_query' => [
                    'generated_params' => ['sort_by' => 'price:asc'],
                    'augmented_params' => ['q' => '*', 'sort_by' => 'date:desc'],
                ],
            ]]])
            ->respond($this->response([], 12));

        $index->search('cheapest shirts')->naturalLanguage()->withoutData()->paginate(5, page: 1);
        $index->search('cheapest shirts')->naturalLanguage()->withoutData()->paginate(5, page: 2);

        $this->assertSame('date:desc', $http->json(0)['searches'][0]['sort_by']);
        $this->assertSame('date:desc', $http->json(1)['searches'][0]['sort_by']);
        $this->assertArrayNotHasKey('nl_query', $http->json(1)['searches'][0]);
    }

    #[Test]
    public function replayed_natural_language_preserves_the_effective_sort_from_a_preset()
    {
        [$index, $http] = $this->makeTypesenseIndex([
            'settings' => [
                'search_parameters' => ['preset' => 'latest'],
                'natural_language' => ['model' => 'articles'],
            ],
        ]);
        $http
            ->respond(['results' => [[
                'found' => 12,
                'hits' => [],
                'parsed_nl_query' => [
                    'generated_params' => ['sort_by' => 'price:asc'],
                    'augmented_params' => ['q' => '*', 'sort_by' => 'date:desc'],
                ],
            ]]])
            ->respond($this->response([], 12));

        $index->search('cheapest shirts')->naturalLanguage()->withoutData()->paginate(5, page: 1);
        $index->search('cheapest shirts')->naturalLanguage()->withoutData()->paginate(5, page: 2);

        $this->assertSame('date:desc', $http->json(1)['searches'][0]['sort_by']);
    }

    private function response(array $hits, int $found): array
    {
        return [
            'results' => [
                compact('found', 'hits'),
            ],
        ];
    }

    private function hit(string $reference, string $title, int $textMatch = 100): array
    {
        return [
            'document' => compact('reference', 'title'),
            'text_match' => $textMatch,
            'highlights' => [],
        ];
    }
}
