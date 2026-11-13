<?php

namespace Tests\Stache\Indexes;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Taxonomies\TermRepository as TermRepositoryContract;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Stache\Repositories\EntryRepository;
use Statamic\Stache\Repositories\TermRepository;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TermEntriesCountTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function index()
    {
        return Stache::store('terms')->store('tags')->index('entries_count');
    }

    private function createContent()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr_FR'],
        ]);

        Taxonomy::make('tags')->sites(['en', 'fr'])->save();
        Taxonomy::make('topics')->sites(['en', 'fr'])->save();
        Collection::make('blog')->sites(['en', 'fr'])->taxonomies(['tags', 'topics'])->save();
        Collection::make('news')->sites(['en'])->taxonomies(['tags'])->dated(true)->futureDateBehavior('private')->save();

        EntryFactory::collection('blog')->id('1')->locale('en')->data(['tags' => ['alfa', 'bravo'], 'topics' => ['alfa']])->create();
        EntryFactory::collection('blog')->id('2')->locale('en')->data(['tags' => ['alfa']])->create();
        EntryFactory::collection('blog')->id('3')->locale('en')->data(['tags' => ['alfa', 'charlie']])->published(false)->create();
        EntryFactory::collection('blog')->id('4')->locale('fr')->data(['tags' => ['alfa', 'delta']])->create();
        EntryFactory::collection('blog')->id('5')->locale('fr')->data(['tags' => ['bravo']])->published(false)->create();
        EntryFactory::collection('news')->id('6')->locale('en')->date('2020-01-01')->data(['tags' => ['alfa', 'echo']])->create();
        EntryFactory::collection('news')->id('7')->locale('en')->date(now()->addYear()->format('Y-m-d'))->data(['tags' => ['bravo']])->create();

        Term::make('alfa')->taxonomy('tags')->data(['title' => 'Alfa'])->save();
        Term::make('foxtrot')->taxonomy('tags')->data(['title' => 'Nothing uses this'])->save();
    }

    #[Test]
    public function it_counts_what_each_term_would_count_for_itself()
    {
        $this->createContent();

        $counts = $this->index()->items()->all();

        $this->assertEquals([
            'en::alfa' => 3,
            'en::bravo' => 1,
            'en::charlie' => 0,
            'en::echo' => 1,
            'en::foxtrot' => 0,
            'fr::alfa' => 1,
            'fr::bravo' => 0,
            'fr::delta' => 1,
            'fr::foxtrot' => 0,
        ], collect($counts)->sortKeys()->all());

        foreach ($counts as $key => $count) {
            [$site, $slug] = explode('::', $key);
            $term = Term::make($slug)->taxonomy('tags')->in($site);

            $this->assertSame(Term::entriesCount($term, 'published'), $count, $key);
        }
    }

    #[Test]
    public function it_finds_which_entries_are_published_once_for_the_whole_index()
    {
        $this->createContent();

        Stache::store('terms')->store('tags')->index('associations')->items();

        $repository = new class(app('stache')) extends EntryRepository
        {
            public $queries = 0;

            public function query()
            {
                $this->queries++;

                return parent::query();
            }
        };

        Entry::swap($repository);

        $this->assertCount(9, $this->index()->items());
        $this->assertSame(1, $repository->queries);
    }

    #[Test]
    public function a_term_updated_by_itself_is_still_counted()
    {
        $this->createContent();
        $this->index()->items();

        EntryFactory::collection('blog')->id('8')->locale('en')->data(['tags' => ['foxtrot']])->create();

        $this->assertSame(1, $this->index()->items()->get('en::foxtrot'));
    }

    #[Test]
    public function a_repository_that_counts_entries_its_own_way_is_asked_for_each_term()
    {
        $this->createContent();

        $repository = new class(app('stache')) extends TermRepository
        {
            public function entriesCount($term, ?string $status = null): int
            {
                return 99;
            }
        };

        $this->app->instance(TermRepositoryContract::class, $repository);
        Term::swap($repository);

        $this->assertEquals([99], collect($this->index()->getItems())->unique()->values()->all());
    }
}
