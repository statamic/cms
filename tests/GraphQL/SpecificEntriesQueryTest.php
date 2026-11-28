<?php

namespace Tests\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Fields\Blueprint;
use Statamic\GraphQL\Queries\SpecificEntriesQuery;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class SpecificEntriesQueryTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_uses_camel_cased_collection_handle_as_query_name()
    {
        Collection::make('blog_posts')->save();
        $this->mockBlueprints('blog_posts', ['post']);

        $query = new SpecificEntriesQuery('blog_posts');

        $this->assertEquals('blogPosts', $this->getQueryName($query));
    }

    #[Test]
    public function it_uses_simple_handle_as_query_name()
    {
        Collection::make('pages')->save();
        $this->mockBlueprints('pages', ['page']);

        $query = new SpecificEntriesQuery('pages');

        $this->assertEquals('pages', $this->getQueryName($query));
    }

    #[Test]
    public function it_does_not_include_collection_arg()
    {
        Collection::make('pages')->save();
        $this->mockBlueprints('pages', ['page']);

        $query = new SpecificEntriesQuery('pages');
        $args = $query->args();

        $this->assertArrayNotHasKey('collection', $args);
        $this->assertArrayHasKey('limit', $args);
        $this->assertArrayHasKey('page', $args);
        $this->assertArrayHasKey('filter', $args);
        $this->assertArrayHasKey('query_scope', $args);
        $this->assertArrayHasKey('sort', $args);
        $this->assertArrayHasKey('site', $args);
    }

    private function mockBlueprints(string $collection, array $handles): void
    {
        $mapped = [];

        foreach ($handles as $handle) {
            $bp = tap($this->partialMock(Blueprint::class), function ($m) use ($handle) {
                $m->shouldReceive('handle')->andReturn($handle);
                $m->shouldReceive('addGqlTypes')->zeroOrMoreTimes();
            });
            $mapped[$handle] = $bp;
        }

        BlueprintRepository::shouldReceive('in')
            ->with("collections/{$collection}")
            ->andReturn(collect($mapped));
        BlueprintRepository::shouldReceive('find')->andReturn(array_values($mapped)[0]);
    }

    private function getQueryName($query): string
    {
        // The Query class does not have a getName() method,
        // so we need to use reflection to get the name.
        $reflection = new \ReflectionProperty($query, 'attributes');

        return $reflection->getValue($query)['name'];
    }
}
