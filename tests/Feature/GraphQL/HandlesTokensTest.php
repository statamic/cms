<?php

namespace Tests\Feature\GraphQL;

use Closure;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Tokens\Token;
use Statamic\Facades\Token as Tokens;
use Tests\TestCase;

#[Group('graphql')]
class HandlesTokensTest extends TestCase
{
    #[Test]
    public function it_handles_tokens()
    {
        $handler = new class
        {
            public function handle(Token $token, $request, Closure $next)
            {
                return ['handled by token' => true];
            }
        };

        $this->app->instance('test-token-handler', $handler);

        $token = Tokens::make('test-token', 'test-token-handler');
        Tokens::shouldReceive('find')->with('test-token')->andReturn($token);
        Tokens::shouldReceive('collectGarbage')->zeroOrMoreTimes();

        $this
            ->post('/graphql?token=test-token', ['query' => '{ping}'])
            ->assertExactJson(['handled by token' => true]);
    }
}
