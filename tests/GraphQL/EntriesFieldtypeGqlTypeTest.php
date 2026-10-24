<?php

namespace Tests\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Entries;
use Statamic\GraphQL\Types\DynamicEntryUnionType;
use Statamic\GraphQL\Types\EntryInterface;
use Statamic\GraphQL\Types\EntryType;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class EntriesFieldtypeGqlTypeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_uses_entry_interface_when_no_collections_are_configured()
    {
        GraphQL::shouldReceive('type')
            ->once()
            ->with('EntryInterface')
            ->andReturn((object) ['name' => 'EntryInterface']);

        GraphQL::shouldReceive('addType')->never();

        $this->fieldtype([
            // no collections configured
            'max_items' => 1,
        ])->toGqlType();
    }

    #[Test]
    public function it_uses_a_concrete_entry_type_when_a_single_blueprint_is_targeted()
    {
        Collection::make('blog_posts')->save();

        /** @var \Statamic\Fields\Blueprint $article */
        $article = tap($this->partialMock(\Statamic\Fields\Blueprint::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('article');
        });
        BlueprintRepository::shouldReceive('in')
            ->with('collections/blog_posts')
            ->andReturn(collect(['article' => $article]));

        EntryInterface::addTypes();

        $expected = EntryType::buildName(Collection::findByHandle('blog_posts'), $article);

        $type = $this->fieldtype([
            'collections' => ['blog_posts'],
            'max_items' => 1,
        ])->toGqlType();

        $this->assertEquals($expected, $type->name);
    }

    #[Test]
    public function it_uses_a_dynamic_union_when_multiple_blueprints_are_possible()
    {
        Collection::make('blog_posts')->save();

        $article = tap($this->partialMock(\Statamic\Fields\Blueprint::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('article');
        });
        $artDirected = tap($this->partialMock(\Statamic\Fields\Blueprint::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('art_directed');
        });

        BlueprintRepository::shouldReceive('in')
            ->with('collections/blog_posts')
            ->andReturn(collect(['article' => $article, 'art_directed' => $artDirected]));

        $collection = Collection::findByHandle('blog_posts');
        $expectedName = DynamicEntryUnionType::getTypeName([
            ['collection' => $collection, 'blueprint' => $article],
            ['collection' => $collection, 'blueprint' => $artDirected],
        ]);

        // Ensure the concrete entry types exist before the union resolves its members.
        EntryInterface::addTypes();

        $type = $this->fieldtype([
            'collections' => ['blog_posts'],
            'max_items' => 1,
        ])->toGqlType();

        $this->assertEquals($expectedName, $type->name);
    }

    private function fieldtype(array $config = []): Entries
    {
        $field = new Field('test', array_merge([
            'type' => 'entries',
        ], $config));

        return (new Entries)->setField($field);
    }
}
