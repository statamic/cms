<?php

namespace Tests\Stache\Stores;

use Facades\Statamic\Stache\Traverser;
use Illuminate\Filesystem\Filesystem;
use Mockery;
use Statamic\Contracts\Globals\GlobalSet;
use Statamic\Facades\GlobalSet as GlobalsAPI;
use Statamic\Facades\Path;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\GlobalsStore;
use Tests\TestCase;

class GlobalsStoreTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        mkdir($this->tempDir = __DIR__.'/tmp');

        $stache = (new Stache)->sites(['en']);
        $this->app->instance(Stache::class, $stache);
        $stache->registerStore($this->store = (new GlobalsStore($stache, app('files')))->directory($this->tempDir));
    }

    public function tearDown(): void
    {
        parent::tearDown();
        (new Filesystem)->deleteDirectory($this->tempDir);
    }

    /** @test */
    public function it_gets_yaml_files_from_the_root()
    {
        touch($this->tempDir.'/one.yaml', 1234567890);
        touch($this->tempDir.'/two.yaml', 1234567890);
        touch($this->tempDir.'/three.txt', 1234567890);
        mkdir($this->tempDir.'/subdirectory');
        touch($this->tempDir.'/subdirectory/nested-one.yaml', 1234567890);
        touch($this->tempDir.'/subdirectory/nested-two.yaml', 1234567890);

        $files = Traverser::filter([$this->store, 'getItemFilter'])->traverse($this->store);

        $dir = Path::tidy($this->tempDir);
        $this->assertEquals([
            $dir.'/one.yaml' => 1234567890,
            $dir.'/two.yaml' => 1234567890,
        ], $files->all());

        // Sanity check. Make sure the file is there but wasn't included.
        $this->assertTrue(file_exists($dir.'/subdirectory/nested-one.yaml'));
        $this->assertTrue(file_exists($dir.'/subdirectory/nested-two.yaml'));
        $this->assertTrue(file_exists($dir.'/three.txt'));
    }

    /** @test */
    public function it_makes_global_set_instances_from_files()
    {
        $item = $this->store->makeItemFromFile(Path::tidy($this->tempDir.'/example.yaml'), "title: Example\ndata:\n  foo: bar");

        $this->assertInstanceOf(GlobalSet::class, $item);
        $this->assertEquals('example', $item->id());
        $this->assertEquals('example', $item->handle());
        $this->assertEquals('Example', $item->title());
        $this->assertEquals(['foo' => 'bar'], $item->in('en')->data()->all());
    }

    /** @test */
    public function it_uses_the_id_as_the_item_key()
    {
        $set = Mockery::mock();
        $set->shouldReceive('id')->andReturn('123');

        $this->assertEquals(
            '123',
            $this->store->getItemKey($set)
        );
    }

    /** @test */
    public function it_saves_to_disk()
    {
        $set = GlobalsAPI::make('test');
        $set->addLocalization($set->makeLocalization('en'));

        $this->store->save($set);

        $this->assertStringEqualsFile($this->tempDir.'/test.yaml', $set->fileContents());
    }

    /** @test */
    public function it_saves_to_disk_with_multiple_sites()
    {
        $this->markTestIncomplete();
    }
}
