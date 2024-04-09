<?php

namespace Tests\Feature\Entries;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class MountingTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
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

        $this->assertEquals($one, Entry::findByUri('/pages/blog/one'));
        $this->assertEquals($two, Entry::findByUri('/pages/blog/two'));

        $mount->slug('diary')->save();

        $this->assertNull(Entry::findByUri('/pages/blog/one'));
        $this->assertNull(Entry::findByUri('/pages/blog/two'));
        $this->assertEquals($one, Entry::findByUri('/pages/diary/one'));
        $this->assertEquals($two, Entry::findByUri('/pages/diary/two'));
    }

    /** @test */
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

        $this->assertEquals($one, Entry::findByUri('/pages/blog/one'));
        $this->assertEquals($two, Entry::findByUri('/pages/blog/two'));

        // Since we're just saving the mount without changing the slug, we don't want to update the URIs.
        $mock = \Mockery::mock(Collection::getFacadeRoot())->makePartial();
        $mock->shouldReceive('updateEntryUris')->never();
        Collection::swap($mock);

        $mount->save();
    }
}
