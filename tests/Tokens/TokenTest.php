<?php

namespace Tests\Tokens;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Tokens\FileToken as Token;
use Tests\TestCase;

class TokenTest extends TestCase
{
    #[Test]
    public function it_uses_a_provided_token_string()
    {
        $token = new Token('foo', 'test');
        $this->assertEquals('foo', $token->token());
    }

    #[Test]
    public function it_generates_a_token_string_when_not_provided()
    {
        $generatedTokens = collect()->times($count = 10, function () {
            return (new Token(null, 'test'))->token();
        });

        $this->assertCount($count, $generatedTokens->unique());
    }

    #[Test]
    public function it_saves_through_the_facade()
    {
        $token = new Token('test', 'test', ['foo' => 'bar']);

        Facades\Token::shouldReceive('save')->with($token)->once()->andReturn('passthru');

        $this->assertEquals('passthru', $token->save()); // the repo save method will return true.
    }

    #[Test]
    public function it_deletes_through_the_facade()
    {
        $token = new Token('test', 'test', ['foo' => 'bar']);

        Facades\Token::shouldReceive('delete')->with($token)->once()->andReturn('passthru');

        $this->assertEquals('passthru', $token->delete()); // the repo delete method will return true.
    }

    #[Test]
    public function it_can_be_handled()
    {
        $this->app->bind('TestTokenHandler', function () {
            return new class()
            {
                public function handle($token, $request, $next)
                {
                    app()->bind('handler-data', function () use ($token) {
                        return $token->data()->all();
                    });

                    return $next($request);
                }
            };
        });

        $token = new Token('test', 'TestTokenHandler', ['foo' => 'bar']);

        $return = $token->handle(new Request, function () {
            return 'response';
        });

        $this->assertEquals(['foo' => 'bar'], app('handler-data'));
        $this->assertEquals('response', $return);
    }

    #[Test]
    public function it_expires_in_one_hour_by_default()
    {
        Carbon::setTestNow(Carbon::create(2020, 1, 1, 3, 0, 0));

        $token = new Token('test', 'test');

        $this->assertInstanceOf(Carbon::class, $token->expiry());
        $this->assertTrue($token->expiry()->eq(Carbon::now()->addHour()));
    }

    #[Test]
    public function it_can_set_a_custom_expiry()
    {
        Carbon::setTestNow(Carbon::create(2020, 1, 1, 3, 0, 0));

        $token = new Token('test', 'test');

        $token->expireAt(Carbon::now()->addHours(3));

        $this->assertInstanceOf(Carbon::class, $token->expiry());
        $this->assertTrue($token->expiry()->eq(Carbon::now()->addHours(3)));
    }

    #[Test]
    public function it_can_check_if_it_has_expired()
    {
        Carbon::setTestNow(Carbon::create(2020, 1, 1, 3, 0, 0));

        $token = (new Token('test', 'test'))->expireAt(Carbon::now()->addHours(3));

        $this->assertFalse($token->hasExpired());

        $this->travel(3)->hours();

        $this->assertFalse($token->hasExpired());
    }
}
