<?php

namespace Tests\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Facades\GlobalSet;
use Statamic\Fields\Blueprint;
use Statamic\GraphQL\Types\GlobalSetInterface;
use Statamic\GraphQL\Types\GlobalSetType;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class GlobalSetInterfaceTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_adds_types()
    {
        GraphQL::spy();

        GlobalSet::make('social_media')->save();
        GlobalSet::make('company_details')->save();
        GlobalSet::make('without_blueprint')->save();
        $social = tap($this->partialMock(Blueprint::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('article');
            $m->shouldReceive('addGqlTypes')->once();
        });
        $company = tap($this->partialMock(Blueprint::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('art_directed');
            $m->shouldReceive('addGqlTypes')->once();
        });
        BlueprintRepository::shouldReceive('find')->with('globals.social_media')->andReturn($social);
        BlueprintRepository::shouldReceive('find')->with('globals.company_details')->andReturn($company);
        BlueprintRepository::shouldReceive('find')->with('globals.without_blueprint')->andReturnNull();

        GlobalSetInterface::addTypes();

        GraphQL::shouldHaveReceived('addTypes')->withArgs(function ($args) {
            $this->assertEveryItemIsInstanceOf(GlobalSetType::class, $args);
            $this->assertEquals($expected = [
                'GlobalSet_SocialMedia',
                'GlobalSet_CompanyDetails',
                'GlobalSet_WithoutBlueprint',
            ], $actual = collect($args)->map->name->all());

            return $actual === $expected;
        });
    }
}
