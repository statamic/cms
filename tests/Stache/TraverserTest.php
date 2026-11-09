<?php

namespace Tests\Stache;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\Assert as PHPUnit;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Path;
use Statamic\Facades\Stache as StacheFacade;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\BasicStore;
use Statamic\Stache\Stores\CollectionsStore;
use Statamic\Stache\Stores\EntriesStore;
use Statamic\Stache\Traverser;
use Symfony\Component\Finder\SplFileInfo;
use Tests\TestCase;

class TraverserTest extends TestCase
{
    private $tempDir;
    private $traverser;

    public function setUp(): void
    {
        parent::setUp();

        $this->tempDir = __DIR__.'/tmp';
        mkdir($this->tempDir);

        $this->traverser = new Traverser();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        (new Filesystem)->deleteDirectory($this->tempDir);
    }

    #[Test]
    public function throws_exception_if_store_doesnt_have_a_directory_defined()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Store [test] does not have a directory defined.');

        $store = Mockery::mock();
        $store->shouldReceive('directory')->andReturnNull();
        $store->shouldReceive('key')->andReturn('test');

        $this->traverser->traverse($store);
    }

    #[Test]
    public function it_gets_no_files_if_directory_doesnt_exist()
    {
        $store = Mockery::mock();
        $store->shouldReceive('directory')->andReturn($this->tempDir.'/non-existent');
        $store->shouldReceive('filter')->andReturnTrue();

        $files = $this->traverser->traverse($store);

        $this->assertInstanceOf(Collection::class, $files);
        $this->assertCount(0, $files);
    }

    #[Test]
    public function gets_files_in_a_stores_directory()
    {
        mkdir($this->tempDir.'/nested');
        touch($this->tempDir.'/one.txt', 1234567893);
        touch($this->tempDir.'/nested/three.txt', 1234567891);
        touch($this->tempDir.'/.hidden.txt', 1234567893);
        touch($this->tempDir.'/two.txt', 1234567892);

        $store = Mockery::mock();
        $store->shouldReceive('directory')->andReturn($this->tempDir);
        $store->shouldReceive('filter')->andReturnTrue();

        $files = $this->traverser->traverse($store);

        $this->assertInstanceOf(Collection::class, $files);
        $this->assertCount(3, $files);
        // We use assertSame because we care about the order.
        // Paths should be output by timestamp.
        $dir = Path::tidy($this->tempDir);
        $this->assertSame([
            $dir.'/nested/three.txt' => 1234567891,
            $dir.'/two.txt' => 1234567892,
            $dir.'/one.txt' => 1234567893,
        ], $files->all());
    }

    #[Test]
    public function files_can_be_filtered()
    {
        touch($this->tempDir.'/one.txt', 1234567891);
        touch($this->tempDir.'/two.yaml', 1234567892);
        touch($this->tempDir.'/three.txt', 1234567893);

        $stache = Mockery::mock(Stache::class);
        $stache->shouldReceive('sites')->andReturn(collect(['en']));
        $store = new class($stache, app('files')) extends BasicStore
        {
            public function key()
            {
            }

            public function makeItemFromFile($path, $contents)
            {
            }
        };
        $store->directory($this->tempDir);

        $filter = function ($file) {
            PHPUnit::assertInstanceOf(SplFileInfo::class, $file);

            return $file->getExtension() === 'txt';
        };

        $files = $this->traverser->filter($filter)->traverse($store);

        $this->assertCount(2, $files);
        $dir = Path::tidy($this->tempDir);
        $this->assertEquals([
            $dir.'/one.txt' => 1234567891,
            $dir.'/three.txt' => 1234567893,
        ], $files->all());
    }

    #[Test]
    public function filtering_does_not_change_the_filter_of_the_original_instance()
    {
        touch($this->tempDir.'/one.txt');
        touch($this->tempDir.'/two.yaml');

        $store = Mockery::mock();
        $store->shouldReceive('directory')->andReturn($this->tempDir);

        $txt = $this->traverser->filter(fn ($file) => $file->getExtension() === 'txt');
        $yaml = $this->traverser->filter(fn ($file) => $file->getExtension() === 'yaml');

        $dir = Path::tidy($this->tempDir);
        $this->assertSame([$dir.'/one.txt'], $txt->traverse($store)->keys()->all());
        $this->assertSame([$dir.'/two.yaml'], $yaml->traverse($store)->keys()->all());
        $this->assertCount(2, $this->traverser->traverse($store));
    }

    #[Test]
    public function a_traversal_started_by_a_filter_does_not_replace_the_running_traversals_filter()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr_FR'],
        ]);

        mkdir($this->tempDir.'/blog/en', 0777, true);
        file_put_contents($this->tempDir.'/blog.yaml', "sites:\n  - en\n  - fr\n");

        foreach (['alpha', 'bravo', 'charlie'] as $slug) {
            file_put_contents($this->tempDir."/blog/en/{$slug}.md", "---\nid: {$slug}\ntitle: {$slug}\n---\n");
        }

        // On multisite, the entries store's filter resolves the collection, and on
        // a cold cache that builds the collections store from disk while the
        // entries traversal is still looping over its files.
        StacheFacade::registerStore((new CollectionsStore)->directory($this->tempDir));
        StacheFacade::registerStore((new EntriesStore)->directory($this->tempDir));

        $this->assertCount(3, StacheFacade::store('entries')->store('blog')->paths());
    }
}
