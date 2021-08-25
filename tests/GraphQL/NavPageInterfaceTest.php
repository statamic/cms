<?php

namespace Tests\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use Statamic\Facades\Blueprint;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Nav;
use Statamic\GraphQL\Types\NavPageInterface;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class NavPageInterfaceTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_gets_the_fields()
    {
        $nav = tap(Nav::make('links'))->save();

        $blueprint = Blueprint::makeFromFields([
            'foo' => ['type' => 'text'],
            'bar' => ['type' => 'integer'],
        ]);
        BlueprintRepository::shouldReceive('find')->with('navigation.links')->andReturn($blueprint);

        $interface = new NavPageInterface($nav);

        $this->assertEquals([
            'foo' => ['type' => GraphQL::string()],
            'bar' => ['type' => GraphQL::int()],
        ], $interface->fields());
    }
}
