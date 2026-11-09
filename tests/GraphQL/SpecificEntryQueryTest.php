<?php

namespace Tests\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Fields\Blueprint;
use Statamic\GraphQL\Queries\SpecificEntryQuery;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class SpecificEntryQueryTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_uses_singular_camel_cased_collection_handle_as_query_name()
    {
        Collection::make('blog_posts')->save();
        $this->mockBlueprints('blog_posts', ['post']);

        $query = new SpecificEntryQuery('blog_posts');

        $this->assertEquals('blogPost', $this->getQueryName($query));
    }

    #[Test]
    public function it_uses_simple_handle_as_query_name()
    {
        Collection::make('pages')->save();
        $this->mockBlueprints('pages', ['page']);

        $query = new SpecificEntryQuery('pages');

        $this->assertEquals('page', $this->getQueryName($query));
    }

    #[Test]
    public function it_appends_entry_suffix_when_singular_name_matches_plural_query_name()
    {
        Collection::make('blog')->save();
        $this->mockBlueprints('blog', ['post']);

        $query = new SpecificEntryQuery('blog');

        $this->assertEquals('blogEntry', $this->getQueryName($query));
    }

    #[Test]
    public function it_does_not_include_collection_arg()
    {
        Collection::make('pages')->save();
        $this->mockBlueprints('pages', ['page']);

        $query = new SpecificEntryQuery('pages');
        $args = $query->args();

        $this->assertArrayNotHasKey('collection', $args);
        $this->assertArrayHasKey('id', $args);
        $this->assertArrayHasKey('slug', $args);
        $this->assertArrayHasKey('uri', $args);
        $this->assertArrayHasKey('filter', $args);
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
        $reflection = new \ReflectionProperty($query, 'attributes');

        return $reflection->getValue($query)['name'];
    }
}
