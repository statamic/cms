<?php

namespace Tests\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Facades\Collection;
use Statamic\Fields\Blueprint;
use Statamic\GraphQL\Types\EntryInterface;
use Statamic\GraphQL\Types\EntryType;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class EntryInterfaceTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_adds_types()
    {
        GraphQL::spy();

        Collection::make('blog_posts')->save();
        Collection::make('menu_items')->save();
        $article = tap($this->partialMock(Blueprint::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('article');
            $m->shouldReceive('addGqlTypes')->once();
        });
        $artDirected = tap($this->partialMock(Blueprint::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('art_directed');
            $m->shouldReceive('addGqlTypes')->once();
        });
        $appetizer = tap($this->partialMock(Blueprint::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('appetizer');
            $m->shouldReceive('addGqlTypes')->once();
        });
        $entree = tap($this->partialMock(Blueprint::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('entree');
            $m->shouldReceive('addGqlTypes')->once();
        });
        BlueprintRepository::shouldReceive('in')->with('collections/blog_posts')->andReturn(collect(['article' => $article, 'art_directed' => $artDirected]));
        BlueprintRepository::shouldReceive('in')->with('collections/menu_items')->andReturn(collect(['appetizer' => $appetizer, 'entree' => $entree]));

        EntryInterface::addTypes();

        GraphQL::shouldHaveReceived('addTypes')->withArgs(function ($args) {
            $this->assertEveryItemIsInstanceOf(EntryType::class, $args);
            $this->assertEquals($expected = [
                'Entry_BlogPosts_Article',
                'Entry_BlogPosts_ArtDirected',
                'Entry_MenuItems_Appetizer',
                'Entry_MenuItems_Entree',
            ], $actual = collect($args)->map->name->all());

            return $actual === $expected;
        });
    }
}
