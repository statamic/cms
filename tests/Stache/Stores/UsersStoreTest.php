<?php

namespace Tests\Stache\Stores;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Statamic\Facades\User as UserAPI;
use Illuminate\Filesystem\Filesystem;
use Facades\Statamic\Stache\Traverser;
use Statamic\Stache\Stores\UsersStore;
use Statamic\Contracts\Auth\User;

class UsersStoreTest extends TestCase
{
    function setUp(): void
    {
        parent::setUp();

        mkdir($this->tempDir = __DIR__.'/tmp');

        $stache = (new Stache)->sites(['en']);
        $this->store = (new UsersStore($stache, app('files')))->directory($this->tempDir);
    }

    function tearDown(): void
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

        $files = Traverser::filter([$this->store, 'getItemFilter'])->traverse($this->store);

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
    function it_makes_user_instances_from_files()
    {
        $item = $this->store->makeItemFromFile($this->tempDir.'/john@example.com.yaml', "id: 123\nname: John Doe\nemail: john@example.com");

        $this->assertInstanceOf(User::class, $item);
        $this->assertEquals('123', $item->id());
        $this->assertEquals('john@example.com', $item->email());
        $this->assertEquals('John Doe', $item->get('name'));
        $this->assertEquals(['name' => 'John Doe', 'email' => 'john@example.com'], $item->data()->all());
    }

    /** @test */
    function it_uses_the_id_as_the_item_key()
    {
        $user = \Mockery::mock();
        $user->shouldReceive('id')->andReturn('123');

        $this->assertEquals(
            '123',
            $this->store->getItemKey($user)
        );
    }
}
