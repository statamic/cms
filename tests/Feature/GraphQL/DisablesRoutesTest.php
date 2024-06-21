<?php

namespace Tests\Feature\GraphQL;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('graphql')]
class DisablesRoutesTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('statamic.graphql.enabled', false);
    }

    #[Test]
    public function it_disables_routes()
    {
        $this
            ->post('/graphql', ['query' => '{ping}'])
            ->assertNotFound();
    }
}
