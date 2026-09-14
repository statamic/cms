<?php

namespace Tests\GraphQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\GraphQL;
use Tests\TestCase;

#[Group('graphql')]
class ManagerTest extends TestCase
{
    #[Test]
    #[DataProvider('introspectionProvider')]
    public function it_gets_introspection_enabled_state($packageEnabled, $statamicConfig, $environment, $expected)
    {
        config(['graphql.security.disable_introspection' => is_null($packageEnabled) ? null : ! $packageEnabled]);
        config(['statamic.graphql.introspection' => $statamicConfig]);
        $this->app['env'] = $environment;

        $this->assertEquals($expected, GraphQL::introspectionEnabled());
    }

    public static function introspectionProvider()
    {
        return [
            'pkg null, statamic null, local' => [null, null, 'local', true],
            'pkg null, statamic null, prod' => [null, null, 'prod', false],

            'pkg enabled, statamic null, local' => [true, null, 'local', true],
            'pkg enabled, statamic null, prod' => [true, null, 'prod', false],
            'pkg enabled, statamic auto, local' => [true, 'auto', 'local', true],
            'pkg enabled, statamic auto, prod' => [true, 'auto', 'prod', false],
            'pkg enabled, statamic enabled, local' => [true, true, 'local', true],
            'pkg enabled, statamic enabled, prod' => [true, true, 'prod', true],
            'pkg enabled, statamic disabled, local' => [true, false, 'local', false],
            'pkg enabled, statamic disabled, prod' => [true, false, 'prod', false],

            'pkg disabled, statamic null, local' => [false, null, 'local', false],
            'pkg disabled, statamic null, prod' => [false, null, 'prod', false],
            'pkg disabled, statamic auto, local' => [false, 'auto', 'local', false],
            'pkg disabled, statamic auto, prod' => [false, 'auto', 'prod', false],
            'pkg disabled, statamic enabled, local' => [false, true, 'local', false],
            'pkg disabled, statamic enabled, prod' => [false, true, 'prod', false],
            'pkg disabled, statamic disabled, local' => [false, false, 'local', false],
            'pkg disabled, statamic disabled, prod' => [false, false, 'prod', false],
        ];
    }
}
