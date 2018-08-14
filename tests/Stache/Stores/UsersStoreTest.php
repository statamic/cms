<?php

namespace Tests\Stache\Stores;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Statamic\API\User as UserAPI;
use Illuminate\Filesystem\Filesystem;
use Facades\Statamic\Stache\Traverser;
use Statamic\Stache\Stores\UsersStore;
use Statamic\Contracts\Data\Users\User;

class UsersStoreTest extends TestCase
{
    function setUp()
    {
        parent::setUp();

        mkdir($this->tempDir = __DIR__.'/tmp');

        $stache = (new Stache)->sites(['en']);
        $this->store = (new UsersStore($stache, app('files')))->directory($this->tempDir);
    }

    function tearDown()
    {
        parent::tearDown();
        (new Filesystem)->deleteDirectory($this->tempDir);
    }

    /** @test */
    function it_gets_yaml_files()
    {
        touch($this->tempDir.'/one.yaml', 1234567890);
        touch($this->tempDir.'/two.yaml', 1234567890);
        touch($this->tempDir.'/three.txt', 1234567890);
        mkdir($this->tempDir.'/subdirectory');
        touch($this->tempDir.'/subdirectory/nested-one.yaml', 1234567890);
        touch($this->tempDir.'/subdirectory/nested-two.yaml', 1234567890);
        touch($this->tempDir.'/top-level-non-yaml-file.md', 1234567890);

        $files = Traverser::traverse($this->store);

        $this->assertEquals([
            $this->tempDir.'/one.yaml' => 1234567890,
            $this->tempDir.'/two.yaml' => 1234567890,
            $this->tempDir.'/subdirectory/nested-one.yaml' => 1234567890,
            $this->tempDir.'/subdirectory/nested-two.yaml' => 1234567890,
        ], $files->all());

        // Sanity check. Make sure the file is there but wasn't included.
        $this->assertTrue(file_exists($this->tempDir.'/three.txt'));
    }

    /** @test */
    function it_makes_user_instances_from_cache()
    {
        $this->markTestIncomplete();

        $items = $this->store->getItemsFromCache([$user]);

        $this->assertCount(1, $items);
        $this->assertInstanceOf(User::class, reset($items));
    }

    /** @test */
    function it_makes_user_instances_from_files()
    {
        $item = $this->store->createItemFromFile($this->tempDir.'/john.yaml', "id: 123\nname: John Doe\nemail: john@example.com");

        $this->assertInstanceOf(User::class, $item);
        $this->assertEquals('123', $item->id());
        $this->assertEquals('john', $item->username());
        $this->assertEquals('john@example.com', $item->email());
        $this->assertEquals('John Doe', $item->get('name'));
        $this->assertEquals(['id' => '123', 'name' => 'John Doe', 'email' => 'john@example.com'], $item->data());
    }

    /** @test */
    function it_uses_the_id_as_the_item_key()
    {
        $user = \Mockery::mock();
        $user->shouldReceive('id')->andReturn('123');

        $this->assertEquals(
            '123',
            $this->store->getItemKey($user, '/path/to/irrelevant.yaml')
        );
    }
}
