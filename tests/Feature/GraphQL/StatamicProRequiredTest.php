<?php

namespace Tests\Feature\GraphQL;

use Statamic\Exceptions\StatamicProAuthorizationException;
use Tests\TestCase;

/** @group graphql */
class StatamicProRequiredTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('statamic.editions.pro', false);
    }

    /** @test */
    public function it_throws_exception_if_pro_is_disabled()
    {
        $this->expectException(StatamicProAuthorizationException::class);

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{ping}'])
            ->assertStatus(500);
    }
}
