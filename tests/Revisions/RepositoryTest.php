<?php

namespace Tests\Revisions;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Revisions\Revision;
use Statamic\Revisions\RevisionRepository;
use Tests\TestCase;

class RepositoryTest extends TestCase
{
    private $repo;

    public function setUp(): void
    {
        parent::setUp();
        config(['statamic.revisions.path' => __DIR__.'/__fixtures__']);
        $this->repo = (new RevisionRepository);
    }

    #[Test]
    public function it_gets_revisions_and_excludes_working_copies()
    {
        $revisions = $this->repo->whereKey('123');

        $this->assertInstanceOf(Collection::class, $revisions);
        $this->assertCount(2, $revisions);
        $this->assertContainsOnlyInstancesOf(Revision::class, $revisions);
    }
}
