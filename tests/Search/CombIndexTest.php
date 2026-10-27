<?php

namespace Tests\Search;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\Filesystem as Files;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Search\Searchable;
use Statamic\Facades\Collection;
use Statamic\Facades\Search;
use Statamic\Search\Comb\Index;
use Statamic\Search\Documents;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CombIndexTest extends TestCase
{
    use IndexTests {
        tearDown as protected indexTestsTearDown;
    }
    use PreventSavingStacheItemsToDisk;

    private $fs;

    private ?string $tmpDir = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->fs = Mockery::mock(Filesystem::class);
        $this->fs->shouldReceive('disk')->andReturn(Mockery::self());
        $this->instance('filesystem', $this->fs);
    }

    public function tearDown(): void
    {
        if ($this->tmpDir && is_dir($this->tmpDir)) {
            foreach (glob($this->tmpDir.'/*') as $file) {
                @unlink($file);
            }
            @rmdir($this->tmpDir);
        }

        $this->indexTestsTearDown();
    }

    protected function beforeSearched()
    {
        $this->fs
            ->shouldReceive('exists')
            ->with('local/storage/search/test.json')
            ->andReturn(true);

        $this->fs
            ->shouldReceive('get')
            ->with('local/storage/search/test.json')
            ->andReturn('[[]]');
    }

    public function getIndexClass()
    {
        return Index::class;
    }

    #[Test]
    public function delete_does_not_rewrite_the_index_when_the_reference_is_absent()
    {
        $path = $this->createIndexFile(['entry::a' => ['title' => 'Foo']]);

        $oldMtime = time() - 3600;
        touch($path, $oldMtime);
        clearstatcache();

        $index = $this->getIndex('test', ['path' => $this->tmpDir], null);

        $document = Mockery::mock(Searchable::class);
        $document->shouldReceive('getSearchReference')->andReturn('entry::missing');

        $index->delete($document);

        clearstatcache();
        $this->assertSame(
            $oldMtime,
            filemtime($path),
            'Comb\Index::delete() rewrote the index file even though the reference was not present.'
        );
    }

    #[Test]
    public function delete_rewrites_the_index_when_the_reference_is_present()
    {
        $path = $this->createIndexFile([
            'entry::a' => ['title' => 'Foo'],
            'entry::b' => ['title' => 'Bar'],
        ]);

        $oldMtime = time() - 3600;
        touch($path, $oldMtime);
        clearstatcache();

        $index = $this->getIndex('test', ['path' => $this->tmpDir], null);

        $document = Mockery::mock(Searchable::class);
        $document->shouldReceive('getSearchReference')->andReturn('entry::a');

        $index->delete($document);

        clearstatcache();
        $this->assertGreaterThan(
            $oldMtime,
            filemtime($path),
            'Comb\Index::delete() did not rewrite the index file even though the reference was present.'
        );

        $this->assertSame(
            ['entry::b' => ['title' => 'Bar']],
            json_decode(file_get_contents($path), true)
        );
    }

    #[Test]
    public function updating_on_a_sync_queue_writes_the_index_file_once()
    {
        $files = $this->prepareUpdate(250);

        Search::index('test')->update();

        $files->shouldHaveReceived('put')->once();
        $this->assertCount(250, json_decode(file_get_contents($this->tmpDir.'/test.json'), true));
    }

    #[Test]
    public function updating_on_a_faked_bus_deletes_the_index_and_writes_nothing()
    {
        Bus::fake();
        $files = $this->prepareUpdate(3);

        Search::index('test')->update();

        $files->shouldNotHaveReceived('put');
        $this->assertFileDoesNotExist($this->tmpDir.'/test.json');
    }

    #[Test]
    public function inserting_outside_an_update_writes_immediately()
    {
        $files = $this->prepareUpdate(0);

        Search::index('test')->insertDocuments(new Documents(['entry::a' => ['title' => 'Foo']]));
        Search::index('test')->insertDocuments(new Documents(['entry::b' => ['title' => 'Bar']]));

        $files->shouldHaveReceived('put')->twice();
        $this->assertCount(3, json_decode(file_get_contents($this->tmpDir.'/test.json'), true));
    }

    private function prepareUpdate(int $entries)
    {
        $this->createIndexFile(['entry::stale' => ['title' => 'Stale']]);

        config([
            'statamic.search.chunk_size' => 100,
            'statamic.search.drivers.local.path' => $this->tmpDir,
            'statamic.search.indexes.test' => ['driver' => 'local', 'searchables' => 'collection:blog', 'fields' => ['title']],
        ]);

        Collection::make('blog')->save();

        for ($i = 1; $i <= $entries; $i++) {
            EntryFactory::collection('blog')->id("entry-$i")->slug("entry-$i")->data(['title' => "Entry $i"])->create();
        }

        $files = Mockery::mock(Files::class)->makePartial();
        $this->instance('files', $files);

        return $files;
    }

    #[Test]
    public function inserting_holds_the_index_lock_across_the_read_and_the_write()
    {
        $path = $this->createIndexFile(['entry::a' => ['title' => 'Foo']]);
        $this->expectIndexLock(
            acquired: fn () => $this->assertSame(['entry::a' => ['title' => 'Foo']], json_decode(file_get_contents($path), true)),
            released: fn () => $this->assertSame(['entry::b' => ['title' => 'Bar'], 'entry::a' => ['title' => 'Foo']], json_decode(file_get_contents($path), true)),
        );

        $index = $this->getIndex('test', ['path' => $this->tmpDir], null);

        $index->insertDocuments(new Documents(['entry::b' => ['title' => 'Bar']]));
    }

    #[Test]
    public function deleting_holds_the_index_lock_across_the_read_and_the_write()
    {
        $path = $this->createIndexFile(['entry::a' => ['title' => 'Foo'], 'entry::b' => ['title' => 'Bar']]);
        $this->expectIndexLock(
            acquired: fn () => $this->assertCount(2, json_decode(file_get_contents($path), true)),
            released: fn () => $this->assertSame(['entry::b' => ['title' => 'Bar']], json_decode(file_get_contents($path), true)),
        );

        $index = $this->getIndex('test', ['path' => $this->tmpDir], null);

        $document = Mockery::mock(Searchable::class);
        $document->shouldReceive('getSearchReference')->andReturn('entry::a');

        $index->delete($document);
    }

    private function expectIndexLock(?\Closure $acquired = null, ?\Closure $released = null)
    {
        $lock = Mockery::mock(Lock::class);
        $lock->shouldReceive('block')->withArgs(fn ($seconds, $callback) => $callback instanceof \Closure)->once()->andReturnUsing(function ($seconds, $callback) use ($acquired, $released) {
            if ($acquired) {
                $acquired();
            }

            $result = $callback();

            if ($released) {
                $released();
            }

            return $result;
        });

        Cache::shouldReceive('lock')->with('search-index-test', 60)->once()->andReturn($lock);

        return $lock;
    }

    private function createIndexFile(array $data): string
    {
        $this->tmpDir = sys_get_temp_dir().'/statamic_comb_test_'.uniqid();
        mkdir($this->tmpDir, 0777, true);
        $path = $this->tmpDir.'/test.json';
        file_put_contents($path, json_encode($data));

        return $path;
    }
}
