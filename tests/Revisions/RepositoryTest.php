<?php

namespace Tests\Revisions;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Revisions\RevisionQueryBuilder;
use Statamic\Facades\User;
use Statamic\Revisions\Revision;
use Statamic\Revisions\RevisionRepository;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\RevisionsStore;
use Tests\TestCase;

class RepositoryTest extends TestCase
{
    private $stache;
    private $directory;
    private $repo;

    public function setUp(): void
    {
        parent::setUp();

        $this->stache = new Stache;
        $this->app->instance(Stache::class, $this->stache);
        $this->directory = __DIR__.'/__fixtures__';
        $this->stache->registerStores([
            (new RevisionsStore)->directory($this->directory),
        ]);
        $this->repo = (new RevisionRepository($this->stache));
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
        User::shouldReceive('find')->andReturnNull();

        $revisions = $this->repo->whereKey('123');

        $this->assertIsArray($revisions->toArray());
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

        $revisions = $builder->where('key', '123')->get();

        $this->assertInstanceOf(Collection::class, $revisions);
        $this->assertCount(2, $revisions);
        $this->assertContainsOnlyInstancesOf(Revision::class, $revisions);

        $revisions = $builder->where('key', '1234')->get();

        $this->assertInstanceOf(Collection::class, $revisions);
        $this->assertCount(0, $revisions);
    }
}
