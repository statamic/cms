<?php

namespace Tests\Stache;

use Mockery;
use Tests\TestCase;
use Statamic\Stache\Stache;
use Statamic\Stache\Traverser;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Statamic\Stache\Stores\BasicStore;
use PHPUnit\Framework\Assert as PHPUnit;
use Symfony\Component\Finder\SplFileInfo;

class TraverserTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->tempDir = __DIR__.'/tmp';
        mkdir($this->tempDir);

        $this->traverser = new Traverser(new Filesystem);
    }

    public function tearDown()
    {
        parent::tearDown();
        (new Filesystem)->deleteDirectory($this->tempDir);
    }

    /** @test */
    function throws_exception_if_store_doesnt_have_a_directory_defined()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Store [test] does not have a directory defined.');

        $store = Mockery::mock();
        $store->shouldReceive('directory')->andReturnNull();
        $store->shouldReceive('key')->andReturn('test');

        $this->traverser->traverse($store);
    }

    /** @test */
    function gets_files_in_a_stores_directory()
    {
        touch($this->tempDir.'/one.txt', 1234567890);
        touch($this->tempDir.'/.hidden.txt', 2345678901);
        touch($this->tempDir.'/two.txt', 3456789012);

        $store = Mockery::mock();
        $store->shouldReceive('directory')->andReturn($this->tempDir);
        $store->shouldReceive('filter')->andReturnTrue();

        $files = $this->traverser->traverse($store);

        $this->assertInstanceOf(Collection::class, $files);
        $this->assertCount(2, $files);
        $this->assertEquals([
            $this->tempDir.'/one.txt' => 1234567890,
            $this->tempDir.'/two.txt' => 3456789012
        ], $files->all());
    }

    /** @test */
    function files_get_filtered_by_stores_closure()
    {
        touch($this->tempDir.'/one.txt', 1234567890);
        touch($this->tempDir.'/two.yaml', 2345678901);
        touch($this->tempDir.'/three.txt', 3456789012);

        $stache = Mockery::mock(Stache::class);
        $stache->shouldReceive('sites')->andReturn(collect(['en']));
        $store = new class($stache) extends BasicStore {
            public function key() { }
            public function getItemsFromCache($cache) { }
            public function filter($file) {
                PHPUnit::assertInstanceOf(SplFileInfo::class, $file);
                return $file->getExtension() === 'txt';
            }
        };
        $store->directory($this->tempDir);

        $files = $this->traverser->traverse($store);

        $this->assertCount(2, $files);
        $this->assertEquals([
            $this->tempDir.'/one.txt' => 1234567890,
            $this->tempDir.'/three.txt' => 3456789012
        ], $files->all());
    }
}
