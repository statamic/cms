<?php

namespace Tests\Revisions;

use Tests\TestCase;
use Statamic\Revisions\Revision;
use Illuminate\Support\Collection;
use Statamic\Revisions\Repository;

class RepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        config(['statamic.revisions.path' => __DIR__.'/__fixtures__']);
        $this->repo = (new Repository);
    }

    /** @test */
    function it_gets_revisions_and_excludes_working_copies()
    {
        $revisions = $this->repo->whereKey('123');

        $this->assertInstanceOf(Collection::class, $revisions);
        $this->assertCount(2, $revisions);
        $this->assertContainsOnlyInstancesOf(Revision::class, $revisions);
    }
}
