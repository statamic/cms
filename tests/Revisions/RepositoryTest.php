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

    #[Test]
    public function it_can_call_to_array_on_a_revision_collection()
    {
        $revisions = $this->repo->whereKey('123');

        $this->assertIsArray($revisions->toArray());
    }

    #[Test]
    public function it_loads_publish_at_as_carbon()
    {
        $revision = $this->repo->whereKey('123')->last();

        $this->assertEquals(1553644800, $revision->publishAt()->timestamp);
    }
}
