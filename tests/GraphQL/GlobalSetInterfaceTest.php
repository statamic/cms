<?php

namespace Tests\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\GlobalFactory;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Fields\Blueprint;
use Statamic\GraphQL\Types\GlobalSetInterface;
use Statamic\GraphQL\Types\GlobalSetType;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class GlobalSetInterfaceTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_adds_types()
    {
        GraphQL::spy();

        GlobalFactory::handle('social_media')->create();
        GlobalFactory::handle('company_details')->create();
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

        GlobalSetInterface::addTypes();

        GraphQL::shouldHaveReceived('addType')->with(GlobalSetInterface::class)->once();
        GraphQL::shouldHaveReceived('addTypes')->withArgs(function ($args) {
            $this->assertEveryItemIsInstanceOf(GlobalSetType::class, $args);
            $this->assertEquals($expected = [
                'GlobalSet_SocialMedia',
                'GlobalSet_CompanyDetails',
            ], $actual = collect($args)->map->name->all());

            return $actual === $expected;
        });
    }
}
