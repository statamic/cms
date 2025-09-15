<?php

namespace Tests\Stache\Repositories;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Entries\Entry;
use Statamic\Entries\EntryCollection;
use Statamic\Exceptions\EntryNotFoundException;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry as EntryAPI;
use Statamic\Stache\Repositories\EntryRepository;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\CollectionsStore;
use Statamic\Stache\Stores\CollectionTreeStore;
use Statamic\Stache\Stores\EntriesStore;
use Statamic\Stache\Stores\NavigationStore;
use Statamic\Stache\Stores\TaxonomiesStore;
use Tests\TestCase;
use Tests\UnlinksPaths;

class EntryRepositoryTest extends TestCase
{
    use UnlinksPaths;

    private $stache;
    private $directory;
    private $repo;

    public function setUp(): void
    {
        parent::setUp();

        $this->stache = (new Stache)->sites(['en', 'fr']);
        $this->app->instance(Stache::class, $this->stache);
        $this->directory = __DIR__.'/../__fixtures__/content/collections';
        $this->stache->registerStores([
            (new CollectionsStore($this->stache, app('files')))->directory($this->directory),
            (new EntriesStore($this->stache, app('files')))->directory($this->directory),
            (new NavigationStore($this->stache, app('files')))->directory(__DIR__.'/../__fixtures__/content/navigation'),
            (new CollectionTreeStore($this->stache, app('files')))->directory(__DIR__.'/../__fixtures__/content/structures/collections'),
            (new TaxonomiesStore($this->stache, app('files')))->directory(__DIR__.'/../__fixtures__/content/taxonomies'),
        ]);

        $this->repo = new EntryRepository($this->stache);
    }

    #[Test]
    public function it_gets_all_entries()
    {
        $entries = $this->repo->all();

        $this->assertInstanceOf(EntryCollection::class, $entries);
        $this->assertCount(14, $entries);
        $this->assertEveryItemIsInstanceOf(Entry::class, $entries);
        $this->assertEquals([
            'alphabetical-alpha',
            'alphabetical-bravo',
            'alphabetical-zulu',
            'blog-christmas',
            'blog-fourth-of-july',
            'numeric-one',
            'numeric-three',
            'numeric-two',
            'pages-about',
            'pages-blog',
            'pages-board',
            'pages-contact',
            'pages-directors',
            'pages-home',
        ], $entries->map->id()->sort()->values()->all());
    }

    #[Test]
    public function it_gets_entries_from_a_collection()
    {
        tap($this->repo->whereCollection('alphabetical'), function ($entries) {
            $this->assertInstanceOf(EntryCollection::class, $entries);
            $this->assertCount(3, $entries);
            $this->assertEveryItemIsInstanceOf(Entry::class, $entries);
            $this->assertEveryItem($entries, function ($item) {
                return $item->collectionHandle() === 'alphabetical';
            });
        });

        tap($this->repo->whereCollection('blog'), function ($entries) {
            $this->assertInstanceOf(EntryCollection::class, $entries);
            $this->assertCount(2, $entries);
            $this->assertEveryItemIsInstanceOf(Entry::class, $entries);
            $this->assertEveryItem($entries, function ($item) {
                return $item->collectionHandle() === 'blog';
            });
        });

        tap($this->repo->whereCollection('numeric'), function ($entries) {
            $this->assertInstanceOf(EntryCollection::class, $entries);
            $this->assertCount(3, $entries);
            $this->assertEveryItemIsInstanceOf(Entry::class, $entries);
            $this->assertEveryItem($entries, function ($item) {
                return $item->collectionHandle() === 'numeric';
            });
        });

        tap($this->repo->whereCollection('pages'), function ($entries) {
            $this->assertInstanceOf(EntryCollection::class, $entries);
            $this->assertCount(6, $entries);
            $this->assertEveryItemIsInstanceOf(Entry::class, $entries);
            $this->assertEveryItem($entries, function ($item) {
                return $item->collectionHandle() === 'pages';
            });
        });
    }

    #[Test]
    public function it_gets_entries_from_multiple_collections()
    {
        $entries = $this->repo->whereInCollection(['alphabetical', 'blog']);

        $this->assertInstanceOf(EntryCollection::class, $entries);
        $this->assertCount(5, $entries);
        $this->assertEveryItemIsInstanceOf(Entry::class, $entries);
        $this->assertEquals([
            'alphabetical-alpha',
            'alphabetical-bravo',
            'alphabetical-zulu',
            'blog-christmas',
            'blog-fourth-of-july',
        ], $entries->map->id()->sort()->values()->all());
    }

    #[Test]
    public function it_gets_entry_by_id()
    {
        $entry = $this->repo->find('alphabetical-bravo');

        $this->assertInstanceOf(Entry::class, $entry);
        $this->assertEquals('Bravo', $entry->get('title'));

        $this->assertNull($this->repo->find('unknown'));
    }

    #[Test]
    public function test_find_or_fail_gets_entry()
    {
        $entry = $this->repo->findOrFail('alphabetical-bravo');

        $this->assertInstanceOf(Entry::class, $entry);
        $this->assertEquals('Bravo', $entry->get('title'));
    }

    #[Test]
    public function test_find_or_fail_throws_exception_when_entry_does_not_exist()
    {
        $this->expectException(EntryNotFoundException::class);
        $this->expectExceptionMessage('Entry [does-not-exist] not found');

        $this->repo->findOrFail('does-not-exist');
    }

    #[Test]
    #[DataProvider('entryByUriProvider')]
    public function it_gets_entry_by_uri($uri, $expectedTitle)
    {
        $entry = $this->repo->findByUri($uri);

        if ($expectedTitle) {
            $this->assertInstanceOf(Entry::class, $entry);
            $this->assertEquals($expectedTitle, $entry->get('title'));
        } else {
            $this->assertNull($entry);
        }
    }

    public static function entryByUriProvider()
    {
        return [
            'case sensitive' => ['/alphabetical/bravo', 'Bravo'],
            'case insensitive' => ['/alphabetical/BrAvO', null],
            'missing' => ['/unknown', null],
        ];
    }

    #[Test]
    public function it_gets_entry_by_structure_uri()
    {
        $entry = $this->repo->findByUri('/about/board/directors');

        $this->assertInstanceOf(Entry::class, $entry);
        $this->assertEquals('pages-directors', $entry->id());
        $this->assertEquals('Directors', $entry->title());
    }

    #[Test, Group('EntryRepository#findByIds')]
    #[DataProvider('entriesByIdsProvider')]
    public function it_gets_entries_by_ids($ids, $expected)
    {
        $actual = $this->repo->findByIds($ids);

        $this->assertInstanceOf(EntryCollection::class, $actual);
        $this->assertEquals($expected, $actual->map->get('title')->all());
    }

    public static function entriesByIdsProvider()
    {
        return [
            'no ids' => [[], []],
            'single' => [['numeric-one'], ['One']],
            'multiple' => [['numeric-one', 'numeric-two', 'numeric-three'], ['One', 'Two', 'Three']],
            'missing' => [['numeric-one', 'unknown', 'numeric-three'], ['One', 'Three']],
            'ordered' => [['numeric-three', 'numeric-one', 'numeric-two'], ['Three', 'One', 'Two']],
        ];
    }

    #[Test]
    public function it_saves_an_entry_to_the_stache_and_to_a_file()
    {
        $entry = EntryAPI::make()
            ->locale('en')
            ->id('test-blog-entry')
            ->collection(Collection::findByHandle('blog'))
            ->slug('test')
            ->published(false)
            ->date('2017-07-04')
            ->data(['foo' => 'bar']);

        $this->unlinkAfter($path = $this->directory.'/blog/2017-07-04.test.md');

        $this->assertCount(14, $this->repo->all());
        $this->assertNull($this->repo->find('test-blog-entry'));

        $this->repo->save($entry);

        $this->assertCount(15, $this->repo->all());
        $this->assertNotNull($item = $this->repo->find('test-blog-entry'));
        $this->assertSame($entry, $item);
        $this->assertArraySubset(['foo' => 'bar'], $item->data());
        $this->assertFileExists($path);
    }

    #[Test]
    public function it_can_delete()
    {
        $entry = EntryAPI::make()
            ->locale('en')
            ->id('test-blog-entry')
            ->collection(Collection::findByHandle('blog'))
            ->slug('test')
            ->published(false)
            ->date('2017-07-04')
            ->data(['foo' => 'bar']);

        $this->unlinkAfter($path = $this->directory.'/blog/2017-07-04.test.md');

        $this->assertCount(14, $this->repo->all());
        $this->assertNull($this->repo->find('test-blog-entry'));

        $this->repo->save($entry);

        $this->assertCount(15, $this->repo->all());
        $this->assertNotNull($item = $this->repo->find('test-blog-entry'));
        $this->assertFileExists($path);

        $this->repo->delete($item);

        $this->assertCount(14, $this->repo->all());
        $this->assertNull($item = $this->repo->find('test-blog-entry'));
        $this->assertFileDoesNotExist($path);
    }
}
