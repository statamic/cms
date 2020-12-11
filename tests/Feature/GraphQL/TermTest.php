<?php

namespace Tests\Feature\GraphQL;

use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class TermTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_queries_a_term_by_id()
    {
        Taxonomy::make('tags')->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alpha')->data(['title' => 'Alpha'])->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('bravo')->data(['title' => 'Bravo'])->save();
        Term::make()->taxonomy('tags')->inDefaultLocale()->slug('charlie')->data(['title' => 'Charlie'])->save();

        $query = <<<'GQL'
{
    term(id: "tags::bravo") {
        id
        title
        slug
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'term' => [
                    'id' => 'tags::bravo',
                    'title' => 'Bravo',
                    'slug' => 'bravo',
                ],
            ]]);
    }
}
