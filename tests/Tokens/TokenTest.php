<?php

namespace Tests\Tokens;

use Statamic\Facades;
use Statamic\Tokens\Token;
use Tests\TestCase;

class TokenTest extends TestCase
{
    /** @test */
    public function it_uses_a_provided_token_string()
    {
        $token = new Token('foo', 'test');
        $this->assertEquals('foo', $token->token());
    }

    /** @test */
    public function it_generates_a_token_string_when_not_provided()
    {
        $generatedTokens = collect()->times($count = 10, function () {
            return (new Token(null, 'test'))->token();
        });

        $this->assertCount($count, $generatedTokens->unique());
    }

    /** @test */
    public function it_saves_through_the_facade()
    {
        $token = new Token('test', 'test', ['foo' => 'bar']);

        Facades\Token::shouldReceive('save')->with($token)->once()->andReturn('passthru');

        $this->assertEquals('passthru', $token->save()); // the repo save method will return true.
    }

    /** @test */
    public function it_can_be_handled()
    {
        $this->app->bind('TestTokenHandler', function () {
            return new class()
            {
                public function handle($token)
                {
                    app()->bind('handler-data', function () use ($token) {
                        return $token->data()->all();
                    });
                }
            };
        });

        $token = new Token('test', 'TestTokenHandler', ['foo' => 'bar']);

        $return = $token->handle();

        $this->assertEquals(['foo' => 'bar'], app('handler-data'));
        $this->assertTrue($return);
    }
}
