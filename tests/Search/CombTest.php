<?php

namespace Tests\Search;

use Statamic\Search\Comb\Comb;
use Statamic\Search\Comb\Exceptions\NoResultsFound;
use Tests\TestCase;

class CombTest extends TestCase
{
    /**
     * @test
     * @dataProvider searchesProvider
     **/
    public function it_searches($term, $expected)
    {
        $comb = new Comb([
            ['title' => 'John Doe', 'email' => 'john@doe.com'],
            ['title' => 'Jane Doe', 'email' => 'jane@doe.com'],
        ]);

        try {
            $results = $comb->lookUp($term);
        } catch (NoResultsFound $e) {
            $results = [];
        }

        $this->assertEquals($expected, collect($results['data'] ?? [])->pluck('data.title')->all());
    }

    public function searchesProvider()
    {
        return [
            'string with single result' => ['jane', ['Jane Doe']],
            'string with multiple results' => ['doe', ['John Doe', 'Jane Doe']],
            'email' => ['john@doe.com', ['John Doe']],
        ];
    }
}
