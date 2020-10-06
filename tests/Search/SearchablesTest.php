<?php

namespace Tests\Search;

use Statamic\Facades\Entry;
use Statamic\Search\Searchables;
use Tests\TestCase;

class SearchablesTest extends TestCase
{
    /** @test */
    public function it_transforms_values_set_in_the_config_file()
    {
        config()->set('statamic.search.indexes.default', [
            'fields' => [
                'title',
            ],
            'transformers' => [
                'title' => function ($value) {
                    return strtoupper($value);
                },
            ],
        ]);

        $index = app(\Statamic\Search\Comb\Index::class, [
            'name' => 'default',
            'config' => config('statamic.search.indexes.default'),
        ]);

        $searchable = Entry::make()->data(['title' => 'Hello']);
        $searchables = new Searchables($index);

        $this->assertEquals([
            'title' => 'HELLO',
        ], $searchables->fields($searchable));
    }

    /** @test */
    public function if_a_transformer_returns_an_array_it_gets_combined_into_the_results()
    {
        config()->set('statamic.search.indexes.default', [
            'fields' => [
                'title',
            ],
            'transformers' => [
                'title' => function ($value) {
                    return [
                        'title' => $value,
                        'title_upper' => strtoupper($value),
                    ];
                },
            ],
        ]);

        $index = app(\Statamic\Search\Comb\Index::class, [
            'name' => 'default',
            'config' => config('statamic.search.indexes.default'),
        ]);

        $searchable = Entry::make()->data(['title' => 'Hello']);
        $searchables = new Searchables($index);

        $this->assertEquals([
            'title' => 'Hello',
            'title_upper' => 'HELLO',
        ], $searchables->fields($searchable));
    }
}
