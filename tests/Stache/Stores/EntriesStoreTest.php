<?php

namespace Tests\Stache\Stores;

use Facades\Statamic\Stache\Traverser;
use Illuminate\Support\Carbon;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
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

    private $parent;
    private $directory;

    public function setUp(): void
    {
        parent::setUp();

        $this->parent = (new EntriesStore)->directory(
            $this->directory = Path::tidy(__DIR__.'/../__fixtures__/content/collections')
        );

        Stache::registerStore($this->parent);

        Stache::store('collections')->directory($this->directory);
    }

    #[Test]
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
                $dir.'/numeric/one.md',
                $dir.'/numeric/two.md',
                $dir.'/numeric/three.md',
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

    #[Test]
    public function it_makes_entry_instances_from_files()
    {
        Facades\Collection::shouldReceive('findByHandle')->with('blog')->andReturn(
            (new \Statamic\Entries\Collection)->handle('blog')->dated(true)
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

    #[Test]
    public function if_slugs_are_not_required_the_filename_still_becomes_the_slug()
    {
        Facades\Collection::shouldReceive('findByHandle')->with('blog')->andReturn(
            (new \Statamic\Entries\Collection)->handle('blog')->requiresSlugs(false)
        );

        $item = $this->parent->store('blog')->makeItemFromFile(
            Path::tidy($this->directory).'/blog/the-slug.md',
            "id: 123\ntitle: Example\nfoo: bar"
        );

        $this->assertEquals('123', $item->id());
        $this->assertEquals('the-slug', $item->slug());
    }

    #[Test]
    public function if_slugs_are_not_required_and_the_filename_is_the_same_as_the_id_then_slug_is_null()
    {
        Facades\Collection::shouldReceive('findByHandle')->with('blog')->andReturn(
            (new \Statamic\Entries\Collection)->handle('blog')->requiresSlugs(false)
        );

        $item = $this->parent->store('blog')->makeItemFromFile(
            Path::tidy($this->directory).'/blog/123.md',
            "id: 123\ntitle: Example\nfoo: bar"
        );

        $this->assertEquals('123', $item->id());
        $this->assertNull($item->slug());
    }

    #[Test]
    public function if_slugs_are_required_and_the_filename_is_the_same_as_the_id_then_slug_is_the_id()
    {
        Facades\Collection::shouldReceive('findByHandle')->with('blog')->andReturn(
            (new \Statamic\Entries\Collection)->handle('blog')->requiresSlugs(true)
        );

        $item = $this->parent->store('blog')->makeItemFromFile(
            Path::tidy($this->directory).'/blog/123.md',
            "id: 123\ntitle: Example\nfoo: bar"
        );

        $this->assertEquals('123', $item->id());
        $this->assertEquals('123', $item->slug());
    }

    #[Test]
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

    #[Test]
    public function it_saves_to_disk()
    {
        $entry = Facades\Entry::make()
            ->id('123')
            ->slug('test')
            ->collection('blog')
            ->date('2017-07-04');

        $this->parent->store('blog')->save($entry);

        $this->assertStringEqualsFile($path = $this->directory.'/blog/2017-07-04.test.md', $entry->fileContents());
        @unlink($path);
        $this->assertFileDoesNotExist($path);

        $this->assertEquals($path, $this->parent->store('blog')->paths()->get('123'));
    }

    #[Test]
    public function it_saves_to_disk_with_modified_path()
    {
        $entry = Facades\Entry::make()
            ->id('123')
            ->slug('test')
            ->collection('blog')
            ->date('2017-07-04');

        $this->parent->store('blog')->save($entry);

        $this->assertStringEqualsFile($initialPath = $this->directory.'/blog/2017-07-04.test.md', $entry->fileContents());
        $this->assertEquals($initialPath, $this->parent->store('blog')->paths()->get('123'));

        $entry->slug('updated');
        $entry->save();

        $this->assertStringEqualsFile($path = $this->directory.'/blog/2017-07-04.updated.md', $entry->fileContents());
        $this->assertEquals($path, $this->parent->store('blog')->paths()->get('123'));

        @unlink($initialPath);
        @unlink($path);
    }

    #[Test]
    public function it_appends_suffix_to_the_filename_if_one_already_exists()
    {
        $existingPath = $this->directory.'/blog/2017-07-04.test.md';
        file_put_contents($existingPath, $existingContents = "---\nid: existing-id\n---");

        $entry = Facades\Entry::make()->id('new-id')->slug('test')->collection('blog')->date('2017-07-04');
        $this->parent->store('blog')->save($entry);
        $newPath = $this->directory.'/blog/2017-07-04.test.1.md';
        $this->assertStringEqualsFile($existingPath, $existingContents);
        $this->assertStringEqualsFile($newPath, $entry->fileContents());

        $anotherEntry = Facades\Entry::make()->id('another-new-id')->slug('test')->collection('blog')->date('2017-07-04');
        $this->parent->store('blog')->save($anotherEntry);
        $anotherNewPath = $this->directory.'/blog/2017-07-04.test.2.md';
        $this->assertStringEqualsFile($existingPath, $existingContents);
        $this->assertStringEqualsFile($anotherNewPath, $anotherEntry->fileContents());

        $this->assertEquals($newPath, $this->parent->store('blog')->paths()->get('new-id'));
        $this->assertEquals($anotherNewPath, $this->parent->store('blog')->paths()->get('another-new-id'));

        @unlink($newPath);
        @unlink($anotherNewPath);
        @unlink($existingPath);
        $this->assertFileDoesNotExist($newPath);
        $this->assertFileDoesNotExist($anotherNewPath);
        $this->assertFileDoesNotExist($existingPath);
    }

    #[Test]
    public function it_doesnt_append_the_suffix_to_the_filename_if_it_is_itself()
    {
        $existingPath = $this->directory.'/blog/2017-07-04.test.md';
        file_put_contents($existingPath, "---\nid: the-id\n---");

        $entry = Facades\Entry::make()
            ->id('the-id')
            ->slug('test')
            ->collection('blog')
            ->date('2017-07-04');

        $this->parent->store('blog')->save($entry);

        $pathWithSuffix = $this->directory.'/blog/2017-07-04.test.1.md';
        $this->assertStringEqualsFile($existingPath, $entry->fileContents());
        $this->assertEquals($existingPath, $this->parent->store('blog')->paths()->get('the-id'));

        @unlink($existingPath);
        $this->assertFileDoesNotExist($pathWithSuffix);
        $this->assertFileDoesNotExist($existingPath);
    }

    #[Test]
    public function it_doesnt_append_the_suffix_to_an_already_suffixed_filename_if_it_is_itself()
    {
        $suffixlessExistingPath = $this->directory.'/blog/2017-07-04.test.md';
        file_put_contents($suffixlessExistingPath, "---\nid: the-id\n---");
        $suffixedExistingPath = $this->directory.'/blog/2017-07-04.test.md';
        file_put_contents($suffixedExistingPath, "---\nid: another-id\n---");

        $entry = Facades\Entry::make()
            ->id('another-id')
            ->slug('test')
            ->collection('blog')
            ->date('2017-07-04');

        $this->parent->store('blog')->save($entry);

        $pathWithIncrementedSuffix = $this->directory.'/blog/2017-07-04.test.2.md';
        $this->assertStringEqualsFile($suffixedExistingPath, $entry->fileContents());
        @unlink($suffixedExistingPath);
        $this->assertFileDoesNotExist($pathWithIncrementedSuffix);
        $this->assertFileDoesNotExist($suffixedExistingPath);

        $this->assertEquals($suffixedExistingPath, $this->parent->store('blog')->paths()->get('another-id'));
    }

    #[Test]
    public function it_keeps_the_suffix_even_if_the_suffixless_path_is_available()
    {
        $existingPath = $this->directory.'/blog/2017-07-04.test.1.md';
        $suffixlessPath = $this->directory.'/blog/2017-07-04.test.md';

        file_put_contents($existingPath, 'id: 123');
        $entry = $this->parent->store('blog')->makeItemFromFile($existingPath, file_get_contents($existingPath));

        $this->parent->store('blog')->save($entry);

        $this->assertStringEqualsFile($existingPath, $entry->fileContents());
        $this->assertFileDoesNotExist($suffixlessPath);

        $this->assertEquals($existingPath, $this->parent->store('blog')->paths()->get('123'));

        @unlink($existingPath);
        $this->assertFileDoesNotExist($existingPath);
    }

    #[Test]
    public function it_removes_the_suffix_if_it_previously_had_one_but_needs_a_new_path_anyway()
    {
        // eg. if the slug is changing, and the filename would be changing anyway,
        // we shouldn't maintain the suffix.

        $existingPath = $this->directory.'/blog/2017-07-04.test.1.md';
        $newPath = $this->directory.'/blog/2017-07-04.updated.md';

        file_put_contents($existingPath, 'id: 123');
        $entry = $this->parent->store('blog')->makeItemFromFile($existingPath, file_get_contents($existingPath));

        $entry->slug('updated');

        $this->parent->store('blog')->save($entry);

        $this->assertStringEqualsFile($newPath, $entry->fileContents());
        $this->assertFileDoesNotExist($existingPath);

        $this->assertEquals($newPath, $this->parent->store('blog')->paths()->get('123'));

        @unlink($newPath);
        $this->assertFileDoesNotExist($newPath);
    }

    #[Test]
    public function it_ignores_entries_in_a_site_subdirectory_where_the_collection_doesnt_have_that_site_enabled()
    {
        $this->markTestIncomplete();
    }
}
