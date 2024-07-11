<?php

namespace Tests\Revisions;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Revisions\Revision;
use Statamic\Revisions\RevisionRepository;
use Statamic\Contracts\Revisions\RevisionQueryBuilder;
use Tests\TestCase;

class RepositoryTest extends TestCase
{
    private $repo;

    public function setUp(): void
    {
        parent::setUp();
        config(['statamic.revisions.path' => __DIR__.'/__fixtures__']);
        $this->repo = (new RevisionRepository($this->app->make('stache')));
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
    public function it_returns_a_query_builder()
    {
        $builder = $this->repo->query();

        $this->assertInstanceOf(RevisionQueryBuilder::class, $builder);
    }

    #[Test]
    public function it_gets_and_filters_items_using_query_builder()
    {
        $builder = $this->repo->query();

        $revisions = $builder->get();

        $this->assertInstanceOf(Collection::class, $revisions);
        $this->assertCount(3, $revisions);
        $this->assertContainsOnlyInstancesOf(Revision::class, $revisions);

        $revisions = $builder->where('key','123')->get();

        $this->assertInstanceOf(Collection::class, $revisions);
        $this->assertCount(2, $revisions);
        $this->assertContainsOnlyInstancesOf(Revision::class, $revisions);

        $revisions = $builder->where('key','1234')->get();

        $this->assertInstanceOf(Collection::class, $revisions);
        $this->assertCount(0, $revisions);
    }
}
