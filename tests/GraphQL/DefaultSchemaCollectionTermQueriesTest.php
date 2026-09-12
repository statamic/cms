<?php

namespace Tests\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;
use Statamic\GraphQL\DefaultSchema;
use Statamic\GraphQL\Queries\SpecificEntriesQuery;
use Statamic\GraphQL\Queries\SpecificEntryQuery;
use Statamic\GraphQL\Queries\SpecificTermQuery;
use Statamic\GraphQL\Queries\SpecificTermsQuery;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class DefaultSchemaCollectionTermQueriesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function enableResource(string $resource): void
    {
        config()->set("statamic.graphql.resources.{$resource}", true);
    }

    private function mockBlueprint(string $namespace, string $handle): void
    {
        $blueprint = tap($this->partialMock(\Statamic\Fields\Blueprint::class), function ($m) use ($handle) {
            $m->shouldReceive('handle')->andReturn($handle);
            $m->shouldReceive('addGqlTypes')->zeroOrMoreTimes();
        });

        BlueprintRepository::shouldReceive('in')
            ->with($namespace)
            ->andReturn(collect([$handle => $blueprint]));
        BlueprintRepository::shouldReceive('find')->andReturn($blueprint);
    }

    #[Test]
    public function it_registers_no_collection_queries_when_config_is_empty()
    {
        $this->enableResource('collections');
        config()->set('statamic.graphql.improved_types.collections', []);

        $queries = $this->getQueryInstances();

        $this->assertEmpty(
            collect($queries)->filter(fn ($q) => $q instanceof SpecificEntriesQuery || $q instanceof SpecificEntryQuery)->all()
        );
    }

    #[Test]
    public function it_registers_collection_queries_for_explicit_handles()
    {
        Collection::make('blog')->save();
        Collection::make('pages')->save();
        $this->mockBlueprint('collections/blog', 'post');
        $this->mockBlueprint('collections/pages', 'page');

        $this->enableResource('collections');
        config()->set('statamic.graphql.improved_types.collections', ['blog']);

        $queries = $this->getQueryInstances();
        $collectionQueries = collect($queries)
            ->filter(fn ($q) => $q instanceof SpecificEntriesQuery || $q instanceof SpecificEntryQuery)
            ->values();

        $this->assertCount(2, $collectionQueries);
        $this->assertEquals(['blog', 'blogEntry'], $collectionQueries->map(fn ($q) => $this->getQueryName($q))->sort()->values()->all());
    }

    #[Test]
    public function it_registers_collection_queries_for_all_when_wildcard_is_used()
    {
        Collection::make('blog')->save();
        Collection::make('pages')->save();
        $this->mockBlueprint('collections/blog', 'post');
        $this->mockBlueprint('collections/pages', 'page');

        $this->enableResource('collections');
        config()->set('statamic.graphql.improved_types.collections', ['*']);

        $queries = $this->getQueryInstances();
        $collectionQueries = collect($queries)
            ->filter(fn ($q) => $q instanceof SpecificEntriesQuery || $q instanceof SpecificEntryQuery)
            ->values();

        $names = $collectionQueries->map(fn ($q) => $this->getQueryName($q))->sort()->values()->all();

        $this->assertCount(4, $collectionQueries);
        $this->assertEquals(['blog', 'blogEntry', 'page', 'pages'], $names);
    }

    #[Test]
    public function it_does_not_register_collection_queries_when_resource_is_disabled()
    {
        Collection::make('blog')->save();
        $this->mockBlueprint('collections/blog', 'post');

        config()->set('statamic.graphql.resources.collections', false);
        config()->set('statamic.graphql.improved_types.collections', ['*']);

        $queries = $this->getQueryInstances();

        $this->assertEmpty(
            collect($queries)->filter(fn ($q) => $q instanceof SpecificEntriesQuery || $q instanceof SpecificEntryQuery)->all()
        );
    }

    #[Test]
    public function it_registers_no_taxonomy_queries_when_config_is_empty()
    {
        $this->enableResource('taxonomies');
        config()->set('statamic.graphql.improved_types.terms', []);

        $queries = $this->getQueryInstances();

        $this->assertEmpty(
            collect($queries)->filter(fn ($q) => $q instanceof SpecificTermsQuery || $q instanceof SpecificTermQuery)->all()
        );
    }

    #[Test]
    public function it_registers_taxonomy_queries_for_explicit_handles()
    {
        Taxonomy::make('tags')->save();
        Taxonomy::make('categories')->save();
        $this->mockBlueprint('taxonomies/tags', 'tag');
        $this->mockBlueprint('taxonomies/categories', 'category');

        $this->enableResource('taxonomies');
        config()->set('statamic.graphql.improved_types.terms', ['tags']);

        $queries = $this->getQueryInstances();
        $termQueries = collect($queries)
            ->filter(fn ($q) => $q instanceof SpecificTermsQuery || $q instanceof SpecificTermQuery)
            ->values();

        $this->assertCount(2, $termQueries);
        $this->assertEquals(['tag', 'tags'], $termQueries->map(fn ($q) => $this->getQueryName($q))->sort()->values()->all());
    }

    #[Test]
    public function it_registers_taxonomy_queries_for_all_when_wildcard_is_used()
    {
        Taxonomy::make('tags')->save();
        Taxonomy::make('categories')->save();
        $this->mockBlueprint('taxonomies/tags', 'tag');
        $this->mockBlueprint('taxonomies/categories', 'category');

        $this->enableResource('taxonomies');
        config()->set('statamic.graphql.improved_types.terms', ['*']);

        $queries = $this->getQueryInstances();
        $termQueries = collect($queries)
            ->filter(fn ($q) => $q instanceof SpecificTermsQuery || $q instanceof SpecificTermQuery)
            ->values();

        $names = $termQueries->map(fn ($q) => $this->getQueryName($q))->sort()->values()->all();

        $this->assertCount(4, $termQueries);
        $this->assertEquals(['categories', 'category', 'tag', 'tags'], $names);
    }

    #[Test]
    public function it_does_not_register_taxonomy_queries_when_resource_is_disabled()
    {
        Taxonomy::make('tags')->save();
        $this->mockBlueprint('taxonomies/tags', 'tag');

        config()->set('statamic.graphql.resources.taxonomies', false);
        config()->set('statamic.graphql.improved_types.terms', ['*']);

        $queries = $this->getQueryInstances();

        $this->assertEmpty(
            collect($queries)->filter(fn ($q) => $q instanceof SpecificTermsQuery || $q instanceof SpecificTermQuery)->all()
        );
    }

    private function getQueryName($query): string
    {
        // The Query class does not have a getName() method,
        // so we need to use reflection to get the name.
        $reflection = new \ReflectionProperty($query, 'attributes');

        return $reflection->getValue($query)['name'];
    }

    private function getQueryInstances(): array
    {
        $schema = app(DefaultSchema::class);
        $config = $schema->getConfig();

        return collect($config['query'])
            ->map(fn ($q) => is_string($q) ? app($q) : $q)
            ->all();
    }
}
