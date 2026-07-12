<?php

namespace Tests\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Facades\AssetContainer;
use Statamic\Fields\Blueprint;
use Statamic\GraphQL\Types\AssetInterface;
use Statamic\GraphQL\Types\AssetType;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class AssetInterfaceTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_adds_types()
    {
        GraphQL::spy();

        Storage::fake('test', ['url' => '/assets']);

        AssetContainer::make('one')->disk('test')->save();
        AssetContainer::make('two')->disk('test')->save();
        $one = tap($this->partialMock(Blueprint::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('article');
            $m->shouldReceive('addGqlTypes')->once();
        });
        $two = tap($this->partialMock(Blueprint::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('art_directed');
            $m->shouldReceive('addGqlTypes')->once();
        });
        BlueprintRepository::shouldReceive('find')->with('assets/one')->andReturn($one);
        BlueprintRepository::shouldReceive('find')->with('assets/two')->andReturn($two);

        AssetInterface::addTypes();

        GraphQL::shouldHaveReceived('addTypes')->withArgs(function ($args) {
            $this->assertEveryItemIsInstanceOf(AssetType::class, $args);
            $this->assertEquals($expected = [
                'Asset_One',
                'Asset_Two',
            ], $actual = collect($args)->map->name->all());

            return $actual === $expected;
        });
    }
}
