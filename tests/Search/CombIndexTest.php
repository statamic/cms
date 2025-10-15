<?php

namespace Tests\Search;

use Illuminate\Contracts\Filesystem\Filesystem;
use Mockery;
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

    public function getIndexClass()
    {
        return Index::class;
    }
}
