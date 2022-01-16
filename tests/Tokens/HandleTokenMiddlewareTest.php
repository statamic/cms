<?php

namespace Tests\Tokens;

use Illuminate\Http\Request;
use Mockery;
use Statamic\Facades\Token as Tokens;
use Statamic\Http\Middleware\HandleToken;
use Statamic\Tokens\Token;
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

    /**
     * @test
     * @dataProvider validTokenProvider
     **/
    public function it_should_handle_valid_tokens($paramToken, $headerToken)
    {
        $token = Mockery::spy(Token::class);
        Tokens::shouldReceive('find')->with('valid-token')->andReturn($token);

        $request = $this->request($paramToken, $headerToken);

        $return = (new HandleToken)->handle($request, function () {
            return 'ok';
        });

        $this->assertEquals('ok', $return);
        $token->shouldHaveReceived('handle')->once();
    }

    public function validTokenProvider()
    {
        return [
            'param' => ['valid-token', null],
            'header' => [null, 'valid-token'],
            'param wins if both provided' => ['valid-token', 'header-token'],
        ];
    }

    /**
     * @test
     * @dataProvider invalidTokenProvider
     **/
    public function it_should_not_handle_invalid_tokens($paramToken, $headerToken)
    {
        Tokens::shouldReceive('find')->with('invalid-token')->once()->andReturnNull();

        $request = $this->request($paramToken, $headerToken);

        $return = (new HandleToken)->handle($request, function () {
            return 'ok';
        });

        $this->assertEquals('ok', $return);
    }

    public function invalidTokenProvider()
    {
        return [
            'param' => ['invalid-token', null],
            'header' => [null, 'invalid-token'],
            'both' => ['invalid-token', 'header-token'],
        ];
    }

    /** @test */
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
