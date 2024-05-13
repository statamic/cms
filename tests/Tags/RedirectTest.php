<?php

namespace Tests\Tags;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Statamic\Facades\Parse;
use Tests\TestCase;

class RedirectTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setSites([
            'en' => ['url' => '/'],
            'fr' => ['url' => '/fr'],
        ]);
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app->booted(function () {
            Route::statamic('/named-route', 'test')->name('named-route');

            Route::statamic('/route-with-params/{foo}/baz', 'test')->name('param-route');
        });
    }

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }

    /** @test */
    public function it_throws_http_response_exception()
    {
        $this->expectException(HttpResponseException::class);
        $this->tag('{{ redirect to="/foo" }}');
    }

    /** @test */
    public function it_redirects_to()
    {
        try {
            $this->tag('{{ redirect to="/foo" }}');
        } catch (HttpResponseException $e) {
            $response = $e->getResponse();
            $this->assertInstanceOf(RedirectResponse::class, $response);
            $this->assertEquals('http://localhost/foo', $response->getTargetUrl());
        }
    }

    /** @test */
    public function it_redirects_to_route()
    {
        try {
            $this->tag('{{ redirect route="named-route" }}');
        } catch (HttpResponseException $e) {
            $response = $e->getResponse();
            $this->assertEquals('http://localhost/named-route', $response->getTargetUrl());
        }
    }

    /** @test */
    public function it_redirects_to_route_with_query_params()
    {
        try {
            $this->tag('{{ redirect route="param-route" foo="bar" }}');
        } catch (HttpResponseException $e) {
            $response = $e->getResponse();
            $this->assertEquals('http://localhost/route-with-params/bar/baz', $response->getTargetUrl());
        }
    }
}
