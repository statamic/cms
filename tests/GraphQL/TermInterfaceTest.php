<?php

namespace Tests\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Facades\Taxonomy;
use Statamic\Fields\Blueprint;
use Statamic\GraphQL\Types\TermInterface;
use Statamic\GraphQL\Types\TermType;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class TermInterfaceTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_adds_types()
    {
        GraphQL::spy();

        Taxonomy::make('product_categories')->save();
        Taxonomy::make('tags')->save();
        $catTypeOne = tap($this->partialMock(Blueprint::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('category_type_one');
            $m->shouldReceive('addGqlTypes')->once();
        });
        $catTypeTwo = tap($this->partialMock(Blueprint::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('category_type_two');
            $m->shouldReceive('addGqlTypes')->once();
        });
        $tagTypeOne = tap($this->partialMock(Blueprint::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('tag_type_one');
            $m->shouldReceive('addGqlTypes')->once();
        });
        $tagTypeTwo = tap($this->partialMock(Blueprint::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('tag_type_two');
            $m->shouldReceive('addGqlTypes')->once();
        });
        BlueprintRepository::shouldReceive('in')->with('taxonomies/product_categories')->andReturn(collect(['category_type_one' => $catTypeOne, 'category_type_two' => $catTypeTwo]));
        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect(['tag_type_one' => $tagTypeOne, 'tag_type_two' => $tagTypeTwo]));

        TermInterface::addTypes();

        GraphQL::shouldHaveReceived('addTypes')->withArgs(function ($args) {
            $this->assertEveryItemIsInstanceOf(TermType::class, $args);
            $this->assertEquals($expected = [
                'Term_ProductCategories_CategoryTypeOne',
                'Term_ProductCategories_CategoryTypeTwo',
                'Term_Tags_TagTypeOne',
                'Term_Tags_TagTypeTwo',
            ], $actual = collect($args)->map->name->all());

            return $actual === $expected;
        });
    }
}
