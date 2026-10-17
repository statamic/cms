<?php

namespace Tests\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Taxonomy;
use Statamic\Fields\Blueprint;
use Statamic\GraphQL\Queries\SpecificTermQuery;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class SpecificTermQueryTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_uses_singular_camel_cased_taxonomy_handle_as_query_name()
    {
        Taxonomy::make('product_categories')->save();
        $this->mockBlueprints('product_categories', ['category']);

        $query = new SpecificTermQuery('product_categories');

        $this->assertEquals('productCategory', $this->getQueryName($query));
    }

    #[Test]
    public function it_uses_simple_handle_as_query_name()
    {
        Taxonomy::make('tags')->save();
        $this->mockBlueprints('tags', ['tag']);

        $query = new SpecificTermQuery('tags');

        $this->assertEquals('tag', $this->getQueryName($query));
    }

    #[Test]
    public function it_appends_term_suffix_when_singular_name_matches_plural_query_name()
    {
        Taxonomy::make('news')->save();
        $this->mockBlueprints('news', ['item']);

        $query = new SpecificTermQuery('news');

        $this->assertEquals('newsTerm', $this->getQueryName($query));
    }

    #[Test]
    public function it_does_not_include_taxonomy_arg()
    {
        Taxonomy::make('tags')->save();
        $this->mockBlueprints('tags', ['tag']);

        $query = new SpecificTermQuery('tags');
        $args = $query->args();

        $this->assertArrayNotHasKey('taxonomy', $args);
        $this->assertArrayHasKey('id', $args);
    }

    private function mockBlueprints(string $taxonomy, array $handles): void
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
            ->with("taxonomies/{$taxonomy}")
            ->andReturn(collect($mapped));
        BlueprintRepository::shouldReceive('find')->andReturn(array_values($mapped)[0]);
    }

    private function getQueryName($query): string
    {
        $reflection = new \ReflectionProperty($query, 'attributes');

        return $reflection->getValue($query)['name'];
    }
}
