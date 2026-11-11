<?php

namespace Tests\Stache\Indexes;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TermAssociationsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function associations()
    {
        return Stache::store('terms')->store('tags')->index('associations');
    }

    private function createEntries()
    {
        Taxonomy::make('tags')->save();
        Collection::make('blog')->taxonomies(['tags'])->save();
        Collection::make('news')->taxonomies(['tags'])->save();

        EntryFactory::collection('blog')->id('1')->data(['tags' => ['Alfa', 'bravo']])->create();
        EntryFactory::collection('blog')->id('2')->data(['tags' => ['bravo']])->create();
        EntryFactory::collection('news')->id('3')->data(['tags' => ['Alfa', 'charlie']])->create();
    }

    #[Test]
    public function it_finds_associations_by_slug_value_and_collection()
    {
        $this->createEntries();

        $index = $this->associations();

        $this->assertEquals(['1', '3'], collect($index->forSlug('alfa'))->pluck('entry')->sort()->values()->all());
        $this->assertEquals(['1', '2'], collect($index->forSlug('bravo'))->pluck('entry')->sort()->values()->all());
        $this->assertSame([], $index->forSlug('delta'));

        // Entries store the value as it was typed; the slug is what a term is found by.
        $this->assertEquals(['1', '3'], collect($index->forValue('Alfa'))->pluck('entry')->sort()->values()->all());
        $this->assertSame([], $index->forValue('alfa'));

        $this->assertEqualsCanonicalizing(['alfa', 'bravo'], $index->slugsInCollections(['blog']));
        $this->assertEqualsCanonicalizing(['alfa', 'charlie'], $index->slugsInCollections(['news']));
        $this->assertEqualsCanonicalizing(['alfa', 'bravo', 'charlie'], $index->slugsInCollections(['blog', 'news']));
        $this->assertSame([], $index->slugsInCollections(['pages']));
    }

    #[Test]
    public function associations_for_several_slugs_come_back_in_the_order_they_are_stored()
    {
        $this->createEntries();

        $index = $this->associations();

        $expected = $index->items()->whereIn('slug', ['charlie', 'alfa'])->all();

        $this->assertSame($expected, $index->forSlugs(['charlie', 'alfa']));
        $this->assertSame($expected, $index->forSlugs(['alfa', 'charlie', 'alfa']));
        $this->assertSame(array_keys($expected), array_keys($index->forSlugs(['alfa', 'charlie'])));
    }

    #[Test]
    public function it_gives_what_reading_through_every_association_gives()
    {
        $this->createEntries();

        $index = $this->associations();

        foreach (['alfa', 'bravo', 'charlie', 'delta'] as $slug) {
            $this->assertSame($index->items()->where('slug', $slug)->all(), $index->forSlug($slug));
        }

        foreach (['Alfa', 'bravo', 'charlie', 'alfa'] as $value) {
            $this->assertSame($index->items()->where('value', $value)->all(), $index->forValue($value));
        }

        foreach ([['blog'], ['news'], ['blog', 'news'], ['pages']] as $collections) {
            $this->assertEqualsCanonicalizing(
                $index->items()->whereIn('collection', $collections)->pluck('slug')->unique()->values()->all(),
                $index->slugsInCollections($collections)
            );
        }
    }

    #[Test]
    public function saving_and_deleting_entries_is_seen_straight_away()
    {
        $this->createEntries();

        $index = $this->associations();
        $this->assertCount(2, $index->forSlug('alfa'));
        $this->assertSame([], $index->forSlug('delta'));

        // A new entry using an existing term and a new one.
        EntryFactory::collection('news')->id('4')->data(['tags' => ['alfa', 'delta']])->create();
        $this->assertEquals(['1', '3', '4'], collect($index->forSlug('alfa'))->pluck('entry')->sort()->values()->all());
        $this->assertEquals(['4'], collect($index->forSlug('delta'))->pluck('entry')->all());
        $this->assertContains('delta', $index->slugsInCollections(['news']));

        // An entry that stops using a term.
        Entry::find('1')->set('tags', ['bravo'])->save();
        $this->assertEquals(['3', '4'], collect($index->forSlug('alfa'))->pluck('entry')->sort()->values()->all());
        $this->assertEqualsCanonicalizing(['bravo'], $index->slugsInCollections(['blog']));

        // The queries built on the index agree.
        $this->assertEquals(['3', '4'], Entry::query()->whereTaxonomy('tags::alfa')->get()->map->id()->sort()->values()->all());
        $this->assertSame(2, Term::entriesCount(Term::find('tags::bravo')));
        $this->assertEquals(['tags::bravo'], Term::query()->where('collection', 'blog')->get()->map->id()->all());
    }

    #[Test]
    public function an_index_that_is_cleared_or_changed_directly_is_grouped_again()
    {
        $this->createEntries();

        $index = $this->associations();
        $this->assertCount(2, $index->forSlug('alfa'));

        $index->push(['value' => 'alfa', 'slug' => 'alfa', 'entry' => '9', 'collection' => 'pages', 'site' => 'en']);
        $this->assertCount(3, $index->forSlug('alfa'));
        $this->assertSame(['alfa'], $index->slugsInCollections(['pages']));

        $index->forgetEntry('9');
        $this->assertCount(2, $index->forSlug('alfa'));
        $this->assertSame([], $index->slugsInCollections(['pages']));

        $index->clear();
        $this->assertCount(2, $index->forSlug('alfa'));
    }
}
