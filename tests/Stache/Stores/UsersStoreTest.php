<?php

namespace Tests\Stache\Stores;

use Facades\Statamic\Stache\Traverser;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Auth\User;
use Statamic\Facades\Path;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\UsersStore;
use Tests\TestCase;

class UsersStoreTest extends TestCase
{
    private $tempDir;
    private $store;

    public function setUp(): void
    {
        parent::setUp();

        mkdir($this->tempDir = __DIR__.'/tmp');

        $stache = (new Stache)->sites(['en']);
        $this->store = (new UsersStore($stache, app('files')))->directory($this->tempDir);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        (new Filesystem)->deleteDirectory($this->tempDir);
    }

    #[Test]
    public function it_gets_yaml_files()
    {
        touch($this->tempDir.'/one.yaml', 1234567890);
        touch($this->tempDir.'/two.yaml', 1234567890);
        touch($this->tempDir.'/three.txt', 1234567890);
        mkdir($this->tempDir.'/subdirectory');
        touch($this->tempDir.'/subdirectory/nested-one.yaml', 1234567890);
        touch($this->tempDir.'/subdirectory/nested-two.yaml', 1234567890);
        touch($this->tempDir.'/top-level-non-yaml-file.md', 1234567890);

        $files = Traverser::filter([$this->store, 'getItemFilter'])->traverse($this->store);

        $dir = Path::tidy($this->tempDir);
        $this->assertEquals([
            $dir.'/one.yaml' => 1234567890,
            $dir.'/two.yaml' => 1234567890,
            $dir.'/subdirectory/nested-one.yaml' => 1234567890,
            $dir.'/subdirectory/nested-two.yaml' => 1234567890,
        ], $files->all());

        // Sanity check. Make sure the file is there but wasn't included.
        $this->assertTrue(file_exists($dir.'/three.txt'));
    }

    #[Test]
    public function it_makes_user_instances_from_files()
    {
        $item = $this->store->makeItemFromFile($this->tempDir.'/john@example.com.yaml', "id: 123\nname: John Doe\nemail: john@example.com");

        $this->assertInstanceOf(User::class, $item);
        $this->assertEquals('123', $item->id());
        $this->assertEquals('john@example.com', $item->email());
        $this->assertEquals('John Doe', $item->get('name'));
        $this->assertEquals(['name' => 'John Doe', 'email' => 'john@example.com'], $item->data()->all());
    }

    #[Test]
    public function it_uses_the_id_as_the_item_key()
    {
        $user = \Mockery::mock();
        $user->shouldReceive('id')->andReturn('123');

        $this->assertEquals(
            '123',
            $this->store->getItemKey($user)
        );
    }
}
