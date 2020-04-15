<?php

namespace Tests\Data\Taxonomies;

use Carbon\Carbon;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Contracts\Taxonomies\Taxonomy as TaxonomyContract;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Fields\Value;
use Statamic\Stache\Query\EntryQueryBuilder;
use Statamic\Taxonomies\AugmentedTerm;
use Tests\Data\AugmentedTestCase;

class AugmentedTermTest extends AugmentedTestCase
{
    /** @test */
    function it_gets_values()
    {
        Carbon::setTestNow('2020-04-15 13:00:00');

        $blueprint = Blueprint::makeFromFields([
            'two' => ['type' => 'text'],
            'unused_in_bp' => ['type' => 'text']
        ]);
        Blueprint::shouldReceive('find')->with('test')->andReturn($blueprint);

        $taxonomy = tap(Taxonomy::make('test'))->save();

        $term = Term::make()
            ->taxonomy('test')
            ->blueprint('test')
            ->in('en')
            ->slug('term-slug')
            ->data([
                'one' => 'the "one" value on the term',
                'two' => 'the "two" value on the term and in the blueprint',
            ]);

        $augmented = new AugmentedTerm($term);

        $expectations = [
            'id'            => ['type' => 'string', 'value' => 'test::term-slug'],
            'slug'          => ['type' => Value::class, 'value' => 'term-slug'],
            'title'         => ['type' => Value::class, 'value' => 'term-slug'],
            'uri'           => ['type' => 'string', 'value' => '/test/term-slug'],
            'url'           => ['type' => 'string', 'value' => '/test/term-slug'],
            'edit_url'      => ['type' => 'string', 'value' => 'http://localhost/cp/taxonomies/test/terms/term-slug/en'],
            'permalink'     => ['type' => 'string', 'value' => 'http://localhost/test/term-slug'],
            'api_url'       => ['type' => 'string', 'value' => 'http://localhost/api/taxonomies/test/terms/term-slug'],
            'is_term'       => ['type' => 'bool', 'value' => true],
            'taxonomy'      => ['type' => TaxonomyContract::class, 'value' => $taxonomy],
            'entries_count' => ['type' => 'int', 'value' => 0],
            'entries'       => ['type' => EntryQueryBuilder::class],
            'one'           => ['type' => 'string', 'value' => 'the "one" value on the term'],
            'two'           => ['type' => Value::class, 'value' => 'the "two" value on the term and in the blueprint'],
            'unused_in_bp'  => ['type' => Value::class, 'value' => null],
        ];

        $this->assertAugmentedCorrectly($expectations, $augmented);
    }
}
