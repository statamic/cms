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

    /**
     * @test
     **/
    public function it_extracts_snippets()
    {
        $content = <<<'EOT'
I would choose pizza. I think. But how then would I stream Teenage Mutant Ninja Turtles?
Perhaps a wired ethernet connection to my Apple TV would be an acceptable loophole. Who
designed this question anyway? Did they mean to imply all pizza, or just wireless
pizza? Wifi is definitely more convenient but I could probably survive just fine
wiring my whole house with Cat5. Or Cat6. Or whatever the latest is, it's hard to pizza.
EOT;

        $comb = new Comb([
            ['content' => $content],
        ]);

        try {
            $results = $comb->lookUp('pizza');
        } catch (NoResultsFound $e) {
            $results = [];
        }

        $expected = [[
            'I would choose pizza. I think. But how then would I stream Teenage Mutant Ninja Turtles? Perhaps a',
            'question anyway? Did they mean to imply all pizza, or just wireless pizza? Wifi is definitely more',
            'just fine wiring my whole house with Cat5. Or Cat6. Or whatever the latest is, it\'s hard to pizza.',
        ]];

        $this->assertEquals($expected, collect($results['data'] ?? [])->pluck('snippets.content')->all());
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
