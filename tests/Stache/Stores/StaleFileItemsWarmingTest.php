<?php

namespace Tests\Stache\Stores;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StaleFileItemsWarmingTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $directory;

    public function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir().'/stache-stale-file-items-'.uniqid();
        mkdir($this->directory.'/tags', 0777, true);

        Stache::store('terms')->directory($this->directory);
        Taxonomy::make('tags')->save();
    }

    public function tearDown(): void
    {
        @unlink($this->directory.'/tags/news.yaml');
        @rmdir($this->directory.'/tags');
        @rmdir($this->directory);

        parent::tearDown();
    }

    #[Test]
    public function a_warm_after_a_clear_indexes_a_file_written_after_an_empty_query()
    {
        $store = Stache::store('terms')->store('tags');

        // An index built on first use, outside a warm, reads the empty directory.
        $this->assertCount(0, $store->index('title')->keys());

        file_put_contents($this->directory.'/tags/news.yaml', "title: News\n");

        $store->clear();
        $store->warm();

        $this->assertCount(1, $store->index('title')->keys());
    }

    #[Test]
    public function a_stache_refresh_indexes_a_file_written_after_an_empty_query()
    {
        $this->assertCount(0, Stache::store('terms')->store('tags')->index('title')->keys());

        file_put_contents($this->directory.'/tags/news.yaml', "title: News\n");

        Stache::refresh();

        $this->assertCount(1, Stache::store('terms')->store('tags')->index('title')->keys());
    }

    #[Test]
    public function a_warm_without_a_clear_indexes_a_file_written_after_an_empty_query()
    {
        $store = Stache::store('terms')->store('tags');

        $this->assertCount(0, $store->index('title')->keys());

        file_put_contents($this->directory.'/tags/news.yaml', "title: News\n");

        // What the Stache does between queue jobs and between Octane requests.
        Stache::stores()->each->resetMemoizedState();
        app('stache.indexes')->each->resetMemoizedState();

        $store->warm();

        $store->resetMemoizedState();
        app('stache.indexes')->each->resetMemoizedState();

        $this->assertCount(1, $store->index('title')->keys());
    }
}
