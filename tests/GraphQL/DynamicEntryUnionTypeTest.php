<?php

namespace Tests\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\GraphQL\Types\DynamicEntryUnionType;
use Statamic\GraphQL\Types\EntryInterface;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class DynamicEntryUnionTypeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_includes_types_from_collections_mounted_on_entries()
    {
        Collection::make('pages')->save();
        $mount = EntryFactory::collection('pages')->slug('blog')->create();

        Collection::make('blog')->mount($mount->id())->save();

        $page = tap($this->partialMock(\Statamic\Fields\Blueprint::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('page');
        });
        $article = tap($this->partialMock(\Statamic\Fields\Blueprint::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('article');
        });

        BlueprintRepository::shouldReceive('in')
            ->with('collections/pages')
            ->andReturn(collect(['page' => $page]));
        BlueprintRepository::shouldReceive('in')
            ->with('collections/blog')
            ->andReturn(collect(['article' => $article]));

        $pages = Collection::findByHandle('pages');
        $blog = Collection::findByHandle('blog');

        EntryInterface::addTypes();

        $expectedName = DynamicEntryUnionType::getTypeName([
            ['collection' => $pages, 'blueprint' => $page],
            ['collection' => $blog, 'blueprint' => $article],
        ]);

        $type = DynamicEntryUnionType::createTypeFor($pages);

        $this->assertEquals($expectedName, $type->name);
    }
}
