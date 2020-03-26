<?php

namespace Tests\Feature\Entries;

use Tests\TestCase;
use Statamic\Facades\Entry;
use Statamic\Facades\Collection;
use Facades\Tests\Factories\EntryFactory;
use Tests\PreventSavingStacheItemsToDisk;

class MountingTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    function updating_a_mounted_page_will_update_the_uris_for_each_entry_in_that_collection()
    {
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
}
