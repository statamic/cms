<?php

namespace Tests\Stache\Stores;

use Facades\Statamic\Stache\Traverser;
use Illuminate\Support\Carbon;
use Mockery;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades;
use Statamic\Facades\Path;
use Statamic\Facades\Stache;
use Statamic\Stache\Stores\EntriesStore;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EntriesStoreTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->parent = (new EntriesStore)->directory(
            $this->directory = __DIR__.'/../__fixtures__/content/collections'
        );

        Stache::registerStore($this->parent);

        Stache::store('collections')->directory($this->directory);
    }

    /** @test */
    public function it_gets_nested_files()
    {
        $dir = Path::tidy($this->directory);

        tap($this->parent->store('alphabetical'), function ($store) use ($dir) {
            $files = Traverser::filter([$store, 'getItemFilter'])->traverse($store);

            $this->assertEquals(collect([
                $dir.'/alphabetical/alpha.md',
                $dir.'/alphabetical/bravo.md',
                $dir.'/alphabetical/zulu.md',
            ])->sort()->values()->all(), $files->keys()->sort()->values()->all());
        });

        tap($this->parent->store('blog'), function ($store) use ($dir) {
            $files = Traverser::filter([$store, 'getItemFilter'])->traverse($store);

            $this->assertEquals(collect([
                $dir.'/blog/2017-25-12.christmas.md',
                $dir.'/blog/2018-07-04.fourth-of-july.md',
            ])->sort()->values()->all(), $files->keys()->sort()->values()->all());
        });

        tap($this->parent->store('numeric'), function ($store) use ($dir) {
            $files = Traverser::filter([$store, 'getItemFilter'])->traverse($store);

            $this->assertEquals(collect([
                $dir.'/numeric/1.one.md',
                $dir.'/numeric/2.two.md',
                $dir.'/numeric/3.three.md',
            ])->sort()->values()->all(), $files->keys()->sort()->values()->all());
        });

        tap($this->parent->store('pages'), function ($store) use ($dir) {
            $files = Traverser::filter([$store, 'getItemFilter'])->traverse($store);

            $this->assertEquals(collect([
                $dir.'/pages/about.md',
                $dir.'/pages/about/board.md',
                $dir.'/pages/about/directors.md',
                $dir.'/pages/blog.md',
                $dir.'/pages/contact.md',
                $dir.'/pages/home.md',
            ])->sort()->values()->all(), $files->keys()->sort()->values()->all());
        });
    }

    /** @test */
    public function it_makes_entry_instances_from_files()
    {
        Facades\Collection::shouldReceive('findByHandle')->with('blog')->andReturn(
            (new \Statamic\Entries\Collection)->dated(true)
        );

        $item = $this->parent->store('blog')->makeItemFromFile(
            Path::tidy($this->directory).'/blog/2017-01-02.my-post.md',
            "id: 123\ntitle: Example\nfoo: bar"
        );

        $this->assertInstanceOf(Entry::class, $item);
        $this->assertEquals('123', $item->id());
        $this->assertEquals('Example', $item->get('title'));
        $this->assertEquals(['title' => 'Example', 'foo' => 'bar'], $item->data()->all());
        $this->assertTrue(Carbon::createFromFormat('Y-m-d H:i', '2017-01-02 00:00')->eq($item->date()));
        $this->assertEquals('my-post', $item->slug());
        $this->assertTrue($item->published());
    }

    /** @test */
    public function it_uses_the_id_of_the_entry_as_the_item_key()
    {
        $entry = Mockery::mock();
        $entry->shouldReceive('id')->andReturn('test');
        $entry->shouldReceive('collectionHandle')->andReturn('example');

        $this->assertEquals(
            'test',
            $this->parent->store('test')->getItemKey($entry)
        );
    }

    /** @test */
    public function it_saves_to_disk()
    {
        $entry = Facades\Entry::make()
            ->id('123')
            ->slug('test')
            ->date('2017-07-04')
            ->collection('blog');

        $this->parent->store('blog')->save($entry);

        $this->assertFileEqualsString($path = $this->directory.'/blog/2017-07-04.test.md', $entry->fileContents());
        @unlink($path);
        $this->assertFileNotExists($path);

        $this->assertEquals($path, $this->parent->store('blog')->paths()->get('123'));
    }

    /** @test */
    public function it_appends_the_id_to_the_filename_if_one_already_exists()
    {
        $existingPath = $this->directory.'/blog/2017-07-04.test.md';
        file_put_contents($existingPath, $existingContents = "---\nid: existing-id\n---");

        $entry = Facades\Entry::make()
            ->id('new-id')
            ->slug('test')
            ->date('2017-07-04')
            ->collection('blog');

        $this->parent->store('blog')->save($entry);

        $newPath = $this->directory.'/blog/2017-07-04.test.new-id.md';
        $this->assertFileEqualsString($existingPath, $existingContents);
        $this->assertFileEqualsString($newPath, $entry->fileContents());
        @unlink($newPath);
        @unlink($existingPath);
        $this->assertFileNotExists($newPath);
        $this->assertFileNotExists($existingPath);

        $this->assertEquals($newPath, $this->parent->store('blog')->paths()->get('new-id'));
    }

    /** @test */
    public function it_doesnt_append_the_id_to_the_filename_if_it_is_itself()
    {
        $existingPath = $this->directory.'/blog/2017-07-04.test.md';
        file_put_contents($existingPath, "---\nid: the-id\n---");

        $entry = Facades\Entry::make()
            ->id('the-id')
            ->slug('test')
            ->date('2017-07-04')
            ->collection('blog');

        $this->parent->store('blog')->save($entry);

        $pathWithIdSuffix = $this->directory.'/blog/2017-07-04.test.the-id.md';
        $this->assertFileEqualsString($existingPath, $entry->fileContents());
        @unlink($existingPath);
        $this->assertFileNotExists($pathWithIdSuffix);
        $this->assertFileNotExists($existingPath);

        $this->assertEquals($existingPath, $this->parent->store('blog')->paths()->get('the-id'));
    }

    /** @test */
    public function it_removes_the_id_suffix_if_the_suffixless_path_is_available()
    {
        $existingPath = $this->directory.'/blog/2017-07-04.test.123.md';
        $expectedPath = $this->directory.'/blog/2017-07-04.test.md';

        $entry = Facades\Entry::make()
            ->id('123')
            ->slug('test')
            ->date('2017-07-04')
            ->collection('blog')
            ->initialPath($existingPath);

        $this->parent->store('blog')->save($entry);

        $this->assertFileNotExists($existingPath);
        $this->assertFileEqualsString($expectedPath, $entry->fileContents());
        @unlink($expectedPath);
        $this->assertFileNotExists($expectedPath);

        $this->assertEquals($expectedPath, $this->parent->store('blog')->paths()->get('123'));
    }

    /** @test */
    public function it_ignores_entries_in_a_site_subdirectory_where_the_collection_doesnt_have_that_site_enabled()
    {
        $this->markTestIncomplete();
    }
}
