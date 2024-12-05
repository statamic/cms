<?php

namespace Tests\Data\Taxonomies;

use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Contracts\Entries\Collection as CollectionContract;
use Statamic\Contracts\Query\Builder as BuilderContract;
use Statamic\Contracts\Taxonomies\Taxonomy as TaxonomyContract;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Statamic\Fields\Value;
use Statamic\Taxonomies\AugmentedTerm;
use Tests\Data\AugmentedTestCase;

class AugmentedTermTest extends AugmentedTestCase
{
    #[Test]
    public function it_gets_values()
    {
        Carbon::setTestNow('2020-04-15 13:00:00');
        User::make()->id('test-user')->save();

        $blueprint = Blueprint::makeFromFields([
            'two' => ['type' => 'text'],
            'unused_in_bp' => ['type' => 'text'],
        ])->setHandle('test');
        Blueprint::shouldReceive('in')->with('taxonomies/test')->andReturn(collect(['test' => $blueprint]));

        $taxonomy = tap(Taxonomy::make('test')
            ->cascade(['three' => 'the "three" value from the taxonomy'])
        )->save();

        $term = Term::make()
            ->taxonomy('test')
            ->blueprint('test')
            ->in('en')
            ->slug('term-slug')
            ->data([
                'one' => 'the "one" value on the term',
                'two' => 'the "two" value on the term and in the blueprint',
                'updated_by' => 'test-user',
                'updated_at' => '1486131000',
            ]);

        $augmented = new AugmentedTerm($term);

        $expectations = [
            'id' => ['type' => 'string', 'value' => 'test::term-slug'],
            'slug' => ['type' => 'string', 'value' => 'term-slug'],
            'title' => ['type' => 'string', 'value' => 'term-slug'],
            'uri' => ['type' => 'string', 'value' => '/test/term-slug'],
            'url' => ['type' => 'string', 'value' => '/test/term-slug'],
            'edit_url' => ['type' => 'string', 'value' => 'http://localhost/cp/taxonomies/test/terms/term-slug/en'],
            'permalink' => ['type' => 'string', 'value' => 'http://localhost/test/term-slug'],
            'api_url' => ['type' => 'string', 'value' => 'http://localhost/api/taxonomies/test/terms/term-slug'],
            'is_term' => ['type' => 'bool', 'value' => true],
            'taxonomy' => ['type' => TaxonomyContract::class, 'value' => $taxonomy],
            'entries_count' => ['type' => 'int', 'value' => 0],
            'entries' => ['type' => BuilderContract::class],
            'one' => ['type' => 'string', 'value' => 'the "one" value on the term'],
            'two' => ['type' => 'string', 'value' => 'the "two" value on the term and in the blueprint'],
            'three' => ['type' => 'string', 'value' => 'the "three" value from the taxonomy'],
            'unused_in_bp' => ['type' => 'string', 'value' => null],
            'locale' => ['type' => 'string', 'value' => 'en'],
            'updated_at' => ['type' => Carbon::class, 'value' => '2017-02-03 14:10'],
            'updated_by' => ['type' => UserContract::class, 'value' => 'test-user'],
            'collection' => ['type' => 'null', 'value' => null],
        ];

        $this->assertAugmentedCorrectly($expectations, $augmented);
    }

    #[Test]
    public function supplemented_title_is_used()
    {
        tap(Taxonomy::make('test'))->save();

        $term = Term::make()
            ->taxonomy('test')
            ->blueprint('test')
            ->in('en')
            ->slug('term-slug')
            ->data(['title' => 'Actual Title'])
            ->setSupplement('title', 'Supplemented Title');

        $augmented = new AugmentedTerm($term);

        $title = $augmented->get('title');
        $this->assertInstanceOf(Value::class, $title);
        $this->assertEquals('Supplemented Title', $title->value());
    }

    #[Test]
    public function collection_is_present_when_set()
    {
        $collection = tap(Collection::make('test'))->save();
        tap(Taxonomy::make('test'))->save();

        $term = Term::make()
            ->taxonomy('test')
            ->blueprint('test')
            ->in('en')
            ->slug('term-slug')
            ->data(['title' => 'Actual Title']);

        $augmented = new AugmentedTerm($term);

        $this->assertNull($augmented->get('collection')->value());

        $term->collection($collection);

        $this->assertInstanceOf(CollectionContract::class, $value = $augmented->get('collection')->value());
        $this->assertEquals($collection->handle(), $value->handle());
    }
}
