<?php

namespace Tests\Tokens;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Tokens\Token;
use Statamic\Facades\Token as Tokens;
use Statamic\Http\Middleware\HandleToken;
use Tests\TestCase;

class HandleTokenMiddlewareTest extends TestCase
{
    private function request($paramToken, $headerToken)
    {
        return Request::create('/test', 'GET',
            ['token' => $paramToken],
            [], [],
            ['HTTP_X_STATAMIC_TOKEN' => $headerToken]
        );
    }

    #[Test]
    #[DataProvider('validTokenProvider')]
    public function it_should_handle_valid_tokens($paramToken, $headerToken)
    {
        $request = $this->request($paramToken, $headerToken);

        $next = function () {
            return new Response;
        };

        $token = Tokens::make('valid-token', TestMiddlewareTokenHandler::class, ['foo' => 'bar']);

        Tokens::shouldReceive('find')->with('valid-token')->andReturn($token);
        Tokens::shouldReceive('collectGarbage')->zeroOrMoreTimes();

        $response = (new HandleToken)->handle($request, $next);

        $this->assertEquals('bar', $request->get('foo'));
        $this->assertEquals('valid-token', $response->headers->get('X-Test-Middleware'));
    }

    public static function validTokenProvider()
    {
        return [
            'param' => ['valid-token', null],
            'header' => [null, 'valid-token'],
            'param wins if both provided' => ['valid-token', 'header-token'],
        ];
    }

    #[Test]
    #[DataProvider('invalidTokenProvider')]
    public function it_should_not_handle_invalid_tokens($paramToken, $headerToken)
    {
        Tokens::shouldReceive('find')->with('invalid-token')->once()->andReturnNull();

        $request = $this->request($paramToken, $headerToken);

        $return = (new HandleToken)->handle($request, function () {
            return 'ok';
        });

        $this->assertEquals('ok', $return);
    }

    public static function invalidTokenProvider()
    {
        return [
            'param' => ['invalid-token', null],
            'header' => [null, 'invalid-token'],
            'both' => ['invalid-token', 'header-token'],
        ];
    }

    #[Test]
    public function it_should_not_handle_missing_tokens()
    {
        Tokens::shouldReceive('find')->never()->andReturnNull();

        $request = $this->request(null, null);

        $return = (new HandleToken)->handle($request, function () {
            return 'ok';
        });

        $this->assertEquals('ok', $return);
    }
}

class TestMiddlewareTokenHandler
{
    public function handle(Token $token, $request, Closure $next)
    {
        $request->query->replace(['foo' => 'bar']);

        $response = $next($request);

        $response->headers->set('X-Test-Middleware', $token->token());

        return $response;
    }
}
