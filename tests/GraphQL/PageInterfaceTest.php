<?php

namespace Tests\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Facades\Collection;
use Statamic\Facades\Nav;
use Statamic\Fields\Blueprint;
use Statamic\GraphQL\Types\PageInterface;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class PageInterfaceTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_adds_types()
    {
        GraphQL::spy();

        Nav::make('first_one')->collections(['blog_posts', 'menu_items'])->save();
        Nav::make('second_one')->collections(['blog_posts'])->save();
        Nav::make('third_one')->save();

        Collection::make('blog_posts')->save();
        Collection::make('menu_items')->save();
        Collection::make('unused_collection')->save();
        $article = tap($this->partialMock(Blueprint::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('article');
            $m->shouldReceive('addGqlTypes')->times(2);
        });
        $artDirected = tap($this->partialMock(Blueprint::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('art_directed');
            $m->shouldReceive('addGqlTypes')->times(2);
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

        PageInterface::addTypes();

        GraphQL::shouldHaveReceived('addTypes')->withArgs(function ($args) {
            $this->assertEquals($expected = [
                'NavPage_FirstOne',
                'NavBasicPage_FirstOne',
                'NavEntryPage_FirstOne_BlogPosts_Article',
                'NavEntryPage_FirstOne_BlogPosts_ArtDirected',
                'NavEntryPage_FirstOne_MenuItems_Appetizer',
                'NavEntryPage_FirstOne_MenuItems_Entree',
                'NavPage_SecondOne',
                'NavBasicPage_SecondOne',
                'NavEntryPage_SecondOne_BlogPosts_Article',
                'NavEntryPage_SecondOne_BlogPosts_ArtDirected',
                'NavPage_ThirdOne',
                'NavBasicPage_ThirdOne',
            ], $actual = collect($args)->map->name->all());

            return $actual === $expected;
        });
    }
}
