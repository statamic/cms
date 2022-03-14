<?php

namespace Tests\Tokens\Handlers;

use Facades\Statamic\CP\LivePreview as CPLivePreview;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use Statamic\Contracts\Tokens\Token;
use Statamic\Tokens\Handlers\LivePreview;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class LivePreviewTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_sets_headers_on_request_and_response()
    {
        $token = Mockery::mock(Token::class);

        CPLivePreview::shouldReceive('item')
            ->with($token)
            ->andReturn(EntryFactory::collection('test')->create());

        $response = new Response;

        $return = (new LivePreview)->handle(
            $token,
            $request = new Request,
            fn () => $response
        );

        $this->assertSame($response, $return);
        $this->assertTrue($request->headers->has('X-Statamic-Live-Preview'));
        $this->assertTrue($response->headers->has('X-Statamic-Live-Preview'));
    }
}
