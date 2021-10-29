<?php

namespace Tests\Search;

use Illuminate\Contracts\Filesystem\Filesystem;
use Mockery;
use Statamic\Auth\UserCollection;
use Statamic\Facades\Search;
use Statamic\Facades\Stache;
use Statamic\Facades\User;
use Statamic\Search\Comb\Index;
use Tests\TestCase;

class CombIndexTest extends TestCase
{
    use IndexTests;

    private $fs;

    public function setUp(): void
    {
        parent::setUp();

        $this->fs = Mockery::mock(Filesystem::class);
        $this->fs->shouldReceive('disk')->andReturn(Mockery::self());
        $this->instance('filesystem', $this->fs);
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

    public function getIndex()
    {
        return app(Index::class);
    }

    /** @test */
    public function it_can_find_users_by_their_email_address()
    {
        config([
            'statamic.search.indexes.users' => [
                'driver' => 'local',
                'searchables' => 'users',
                'fields' => ['name', 'email'],
            ]
        ]);

        $john = User::make()
            ->set('name', 'John Doe')
            ->email('john@doe.com')
            ->save();

        $jane = User::make()
            ->set('name', 'Jane Doe')
            ->email('jane@doe.com')
            ->save();

        $index = Search::index('users');

        $index->update();

        $users = $index->search('john@doe.com')->get();

        $this->assertCount(1, $users, 'User could not be found by his email address');
        $this->assertEquals($john->id(), $users->first()->id());
        $this->assertNotEquals($jane->id(), $users->first()->id());

        // Clean up the created users
        $path = __DIR__.'/../__fixtures__/users';
        $this->files = app(\Illuminate\Filesystem\Filesystem::class);
        $this->files->cleanDirectory($path);
        $this->files->put($path.'/.gitkeep', null);
    }
}
