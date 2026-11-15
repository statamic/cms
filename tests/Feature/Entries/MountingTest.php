<?php

namespace Tests\Feature\Entries;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class MountingTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function tearDown(): void
    {
        Cache::store('file')->flush();

        parent::tearDown();
    }

    #[Test]
    public function updating_a_mounted_page_will_update_the_uris_for_each_entry_in_that_collection()
    {
        config(['cache.default' => 'file']); // Doesn't work when they're arrays since the object is stored in memory.
        Cache::clear();

        Collection::make('pages')->routes('pages/{slug}')->save();

        EntryFactory::collection('pages')->slug('another-page')->create();
        $mount = EntryFactory::collection('pages')->slug('blog')->create();
        Collection::make('blog')->routes('{mount}/{slug}')->mount($mount->id())->save();

        $one = EntryFactory::collection('blog')->slug('one')->create();
        $two = EntryFactory::collection('blog')->slug('two')->create();

        $this->assertEquals($one->id(), Entry::findByUri('/pages/blog/one')->id());
        $this->assertEquals($two->id(), Entry::findByUri('/pages/blog/two')->id());

        $mount->slug('diary')->save();

        $this->assertNull(Entry::findByUri('/pages/blog/one'));
        $this->assertNull(Entry::findByUri('/pages/blog/two'));
        $this->assertEquals($one->id(), Entry::findByUri('/pages/diary/one')->id());
        $this->assertEquals($two->id(), Entry::findByUri('/pages/diary/two')->id());
    }

    #[Test]
    public function updating_a_mounted_page_will_not_update_the_uris_when_slug_is_clean()
    {
        config(['cache.default' => 'file']); // Doesn't work when they're arrays since the object is stored in memory.
        Cache::clear();

        Collection::make('pages')->routes('pages/{slug}')->save();

        EntryFactory::collection('pages')->slug('another-page')->create();
        $mount = EntryFactory::collection('pages')->slug('blog')->create();
        Collection::make('blog')->routes('{mount}/{slug}')->mount($mount->id())->save();

        $one = EntryFactory::collection('blog')->slug('one')->create();
        $two = EntryFactory::collection('blog')->slug('two')->create();

        $this->assertEquals($one->id(), Entry::findByUri('/pages/blog/one')->id());
        $this->assertEquals($two->id(), Entry::findByUri('/pages/blog/two')->id());

        // Since we're just saving the mount without changing the slug, we don't want to update the URIs.
        $mock = \Mockery::mock(Collection::getFacadeRoot())->makePartial();
        $mock->shouldReceive('updateEntryUris')->never();
        Collection::swap($mock);

        $mount->save();
    }

    #[Test]
    public function updating_the_parent_of_a_mounted_page_will_update_the_uris_for_each_entry_in_that_collection()
    {
        config(['cache.default' => 'file']); // Doesn't work when they're arrays since the object is stored in memory.
        Cache::clear();

        $this->makeStructuredPages();
        Collection::make('blog')->routes('{mount}/{slug}')->mount('blog')->save();
        $one = EntryFactory::collection('blog')->slug('one')->create();

        $this->assertEquals($one->id(), Entry::findByUri('/about/blog/one')->id());

        Entry::find('about')->slug('info')->save();

        $this->assertNull(Entry::findByUri('/about/blog/one'));
        $this->assertEquals($one->id(), Entry::findByUri('/info/blog/one')->id());
    }

    #[Test]
    public function moving_a_mounted_page_in_the_tree_will_update_the_uris_for_each_entry_in_that_collection()
    {
        config(['cache.default' => 'file']); // Doesn't work when they're arrays since the object is stored in memory.
        Cache::clear();

        $pages = $this->makeStructuredPages();
        Collection::make('blog')->routes('{mount}/{slug}')->mount('blog')->save();
        $one = EntryFactory::collection('blog')->slug('one')->create();

        $this->assertEquals($one->id(), Entry::findByUri('/about/blog/one')->id());

        $pages->structure()->in('en')->tree([
            ['entry' => 'about'],
            ['entry' => 'contact', 'children' => [['entry' => 'blog']]],
        ])->save();

        $this->assertNull(Entry::findByUri('/about/blog/one'));
        $this->assertEquals($one->id(), Entry::findByUri('/contact/blog/one')->id());
    }

    #[Test]
    public function moving_a_localized_mounted_page_in_the_tree_will_update_the_uris_for_each_entry_in_that_collection()
    {
        config(['cache.default' => 'file']); // Doesn't work when they're arrays since the object is stored in memory.
        Cache::clear();

        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr'],
        ]);

        $pages = $this->makeStructuredPages(['en', 'fr']);
        collect(['about', 'contact', 'blog'])->each(
            fn ($id) => Entry::find($id)->makeLocalization('fr')->id("{$id}-fr")->save()
        );
        $pages->structure()->in('fr')->tree([
            ['entry' => 'about-fr', 'children' => [['entry' => 'blog-fr']]],
            ['entry' => 'contact-fr'],
        ])->save();

        Collection::make('blog')->sites(['en', 'fr'])->routes('{mount}/{slug}')->mount('blog')->save();
        $one = EntryFactory::collection('blog')->locale('fr')->slug('one')->create();

        $this->assertEquals($one->id(), Entry::findByUri('/about/blog/one', 'fr')->id());

        $pages->structure()->in('fr')->tree([
            ['entry' => 'about-fr'],
            ['entry' => 'contact-fr', 'children' => [['entry' => 'blog-fr']]],
        ])->save();

        $this->assertNull(Entry::findByUri('/about/blog/one', 'fr'));
        $this->assertEquals($one->id(), Entry::findByUri('/contact/blog/one', 'fr')->id());
    }

    private function makeStructuredPages(array $sites = ['en'])
    {
        $pages = tap(Collection::make('pages')->sites($sites)->routes('{parent_uri}/{slug}')->structureContents(['root' => false]))->save();

        EntryFactory::id('about')->collection('pages')->slug('about')->create();
        EntryFactory::id('contact')->collection('pages')->slug('contact')->create();
        EntryFactory::id('blog')->collection('pages')->slug('blog')->create();

        $pages->structure()->in('en')->tree([
            ['entry' => 'about', 'children' => [['entry' => 'blog']]],
            ['entry' => 'contact'],
        ])->save();

        return $pages;
    }
}
