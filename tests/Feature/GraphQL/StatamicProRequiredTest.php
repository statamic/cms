<?php

namespace Tests\Feature\GraphQL;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Exceptions\StatamicProAuthorizationException;
use Tests\TestCase;

#[Group('graphql')]
class StatamicProRequiredTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('statamic.editions.pro', false);
    }

    #[Test]
    public function it_throws_exception_if_pro_is_disabled()
    {
        $this->expectException(StatamicProAuthorizationException::class);

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{ping}'])
            ->assertStatus(500);
    }
}
