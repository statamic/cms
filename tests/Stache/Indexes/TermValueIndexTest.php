<?php

namespace Tests\Stache\Indexes;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TermValueIndexTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function createEntries()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr_FR'],
        ]);

        Taxonomy::make('tags')->sites(['en', 'fr'])->save();
        Collection::make('blog')->taxonomies(['tags'])->sites(['en', 'fr'])->save();
        Collection::make('news')->taxonomies(['tags'])->sites(['en', 'fr'])->save();

        // Most terms are used by several entries, in more than one collection and site.
        EntryFactory::collection('blog')->id('1')->data(['tags' => ['Alfa', 'bravo']])->create();
        EntryFactory::collection('blog')->id('2')->data(['tags' => ['bravo', 'charlie']])->create();
        EntryFactory::collection('news')->id('3')->data(['tags' => ['alfa', 'charlie']])->create();
        EntryFactory::collection('news')->id('4')->locale('fr')->data(['tags' => ['alfa', 'delta']])->create();
        EntryFactory::collection('blog')->id('5')->locale('fr')->data(['tags' => ['Alfa']])->create();

        // A term with a file (so in every site) wins over the term made from its associations.
        Term::make('bravo')->taxonomy('tags')->dataForLocale('en', ['title' => 'Bravo from file'])->save();
    }

    /**
     * What the index held when a term was made for every association, rather than once per term.
     */
    private function itemsFromEveryAssociation($index): array
    {
        $store = Stache::store('terms')->store('tags');

        return $store->index('associations')->items()
            ->filter()
            ->mapWithKeys(function ($association) use ($index) {
                $term = Term::make($value = $association['slug'])
                    ->taxonomy('tags')
                    ->set('title', $value)
                    ->in($association['site']);

                return [$term->locale().'::'.$term->slug() => $index->getItemValue($term)];
            })
            ->merge((new \Statamic\Stache\Indexes\Value($store, $index->name()))->getItems())
            ->all();
    }

    #[Test]
    public function it_gives_what_making_a_term_for_every_association_gives()
    {
        $this->createEntries();

        foreach (['title', 'slug', 'site', 'id', 'path'] as $name) {
            $index = Stache::store('terms')->store('tags')->index($name);

            $this->assertSame($this->itemsFromEveryAssociation($index), $index->getItems(), "The [{$name}] index differs.");
        }
    }

    #[Test]
    public function it_has_one_item_per_term_and_site()
    {
        $this->createEntries();

        $titles = Stache::store('terms')->store('tags')->index('title')->getItems();

        $this->assertSame([
            'en::alfa' => 'alfa',
            'en::bravo' => 'Bravo from file',
            'en::charlie' => 'charlie',
            'fr::alfa' => 'alfa',
            'fr::bravo' => 'Bravo from file',
            'fr::delta' => 'delta',
        ], collect($titles)->sortKeys()->all());
    }
}
