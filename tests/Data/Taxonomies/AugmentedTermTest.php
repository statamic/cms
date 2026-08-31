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
            'parent' => ['type' => 'null', 'value' => null],
            'children' => ['type' => 'null', 'value' => null],
            'ancestors' => ['type' => 'null', 'value' => null],
            'depth' => ['type' => 'null', 'value' => null],
            'is_root' => ['type' => 'null', 'value' => null],
        ];

        $this->assertAugmentedCorrectly($expectations, $augmented);
    }

    #[Test]
    public function flat_taxonomy_blueprint_fields_with_reserved_hierarchy_handles_are_not_shadowed()
    {
        $blueprint = Blueprint::makeFromFields([
            'parent' => ['type' => 'text'],
            'children' => ['type' => 'text'],
            'ancestors' => ['type' => 'text'],
            'depth' => ['type' => 'integer'],
            'is_root' => ['type' => 'toggle'],
        ])->setHandle('test');
        Blueprint::shouldReceive('in')->with('taxonomies/test')->andReturn(collect(['test' => $blueprint]));

        tap(Taxonomy::make('test'))->save();

        $term = Term::make()
            ->taxonomy('test')
            ->blueprint('test')
            ->in('en')
            ->slug('term-slug')
            ->data([
                'parent' => 'the parent field value',
                'children' => 'the children field value',
                'ancestors' => 'the ancestors field value',
                'depth' => 5,
                'is_root' => true,
            ]);

        $augmented = new AugmentedTerm($term);

        $this->assertEquals('the parent field value', $augmented->get('parent')->value());
        $this->assertEquals('the children field value', $augmented->get('children')->value());
        $this->assertEquals('the ancestors field value', $augmented->get('ancestors')->value());
        $this->assertEquals(5, $augmented->get('depth')->value());
        $this->assertTrue($augmented->get('is_root')->value());
    }

    #[Test]
    public function hierarchical_taxonomy_returns_structural_values_even_when_blueprint_defines_those_fields()
    {
        $blueprint = Blueprint::makeFromFields([
            'parent' => ['type' => 'text'],
        ])->setHandle('test');
        Blueprint::shouldReceive('in')->with('taxonomies/test')->andReturn(collect(['test' => $blueprint]));

        $taxonomy = tap(Taxonomy::make('test')->structureContents([]))->save();

        $root = tap(Term::make('animals')->taxonomy('test')->blueprint('test')->data(['title' => 'Animals', 'parent' => 'ignored field value']))->save();
        $child = tap(Term::make('cat')->taxonomy('test')->blueprint('test')->data(['title' => 'Cat', 'parent' => 'ignored field value']))->save();

        $taxonomy->structure()->tree()->tree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
        ])->save();

        $augmented = new AugmentedTerm($child->in('en'));

        $this->assertEquals(2, $augmented->get('depth')->value());
        $this->assertFalse($augmented->get('is_root')->value());
        $this->assertEquals('test::animals', $augmented->get('parent')->value()->id());
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
