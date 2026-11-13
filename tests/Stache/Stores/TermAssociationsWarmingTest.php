<?php

namespace Tests\Stache\Stores;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TermAssociationsWarmingTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function warming_builds_each_taxonomys_associations_once()
    {
        Taxonomy::make('tags')->save();
        Collection::make('blog')->taxonomies(['tags'])->save();
        EntryFactory::collection('blog')->id('1')->data(['tags' => ['alfa', 'bravo']])->create();
        EntryFactory::collection('blog')->id('2')->data(['tags' => ['bravo']])->create();

        Stache::clear();

        // Building the associations queries each of the taxonomy's collections for
        // entries that use it, so the number of those queries is the number of builds.
        config(['statamic.system.fake_sql_queries' => true]);
        $queries = [];
        Event::listen(QueryExecuted::class, function ($event) use (&$queries) {
            if (str_contains($event->sql, 'tags')) {
                $queries[] = $event->sql;
            }
        });

        Stache::store('terms')->warm();

        $this->assertCount(1, $queries, 'The associations were built '.count($queries).' times.');

        $associations = Stache::store('terms')->store('tags')->index('associations');
        $this->assertEquals(['1', '2'], collect($associations->forSlug('bravo'))->pluck('entry')->sort()->values()->all());
        $this->assertEquals(
            ['en::alfa', 'en::bravo'],
            collect(Stache::store('terms')->store('tags')->index('slug')->items())->keys()->sort()->values()->all()
        );
    }

    #[Test]
    public function saving_an_entry_without_changing_its_terms_leaves_the_associations_as_they_were()
    {
        Taxonomy::make('tags')->save();
        Collection::make('blog')->taxonomies(['tags'])->save();
        EntryFactory::collection('blog')->id('1')->data(['title' => 'One', 'tags' => ['alfa', 'bravo']])->create();
        $entry = EntryFactory::collection('blog')->id('2')->data(['title' => 'Two', 'tags' => ['bravo']])->create();

        $associations = fn () => Stache::store('terms')->store('tags')->index('associations')->items()->all();

        $before = $associations();

        $entry->set('title', 'Two, edited')->save();

        // The same rows under the same keys, where they used to be taken out and added again at the end.
        $this->assertSame($before, $associations());

        $entry->set('tags', ['bravo', 'charlie'])->save();

        $this->assertEquals(
            [['1', 'alfa'], ['1', 'bravo'], ['2', 'bravo'], ['2', 'charlie']],
            collect($associations())->map(fn ($row) => [$row['entry'], $row['slug']])->sort()->values()->all()
        );

        $entry->set('tags', [])->save();

        $this->assertEquals(['1', '1'], collect($associations())->pluck('entry')->values()->all());
    }

    #[Test]
    public function a_terms_indexes_are_still_updated_when_an_entrys_terms_havent_changed()
    {
        Taxonomy::make('tags')->save();
        Collection::make('blog')->taxonomies(['tags'])->save();
        $entry = EntryFactory::collection('blog')->id('1')->data(['tags' => ['alfa']])->create();

        $count = fn () => Stache::store('terms')->store('tags')->index('entries_count')->items()->get('en::alfa');

        $this->assertSame(1, $count());

        $entry->published(false)->save();

        $this->assertSame(0, $count());
    }
}
