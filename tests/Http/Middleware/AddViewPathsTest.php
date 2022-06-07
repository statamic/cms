<?php

namespace Tests\Http\Middleware;

use Illuminate\Http\Request;
use Statamic\Facades\Site;
use Statamic\Http\Middleware\AddViewPaths;
use Statamic\Support\Str;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AddViewPathsTest extends TestCase
{
    /**
     * @test
     */
    public function adds_site_paths()
    {
        Site::setConfig(['sites' => [
            'english' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'french' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]]);
        $this->assertCount(2, view()->getFinder()->getPaths());

        $symfonyRequest = SymfonyRequest::create('/');

        $request = Request::createFromBase($symfonyRequest);

        Site::setCurrent('french');
        $response = (new AddViewPaths())->handle(
            $request,
            fn () => new Response()
        );

        $paths = view()->getFinder()->getPaths();
        $this->assertCount(4, $paths);
        $this->assertTrue(Str::endsWith($paths[0], 'french'));
        $this->assertTrue(Str::endsWith($paths[1], 'views'));
        $this->assertTrue(Str::endsWith($paths[2], 'french'));
        $this->assertTrue(Str::endsWith($paths[3], 'views'));
    }

    /**
     * @test
     */
    public function middleware_attached_to_routes()
    {
        /** @var Router $router */
        $router = app('router');
        $this->assertTrue(in_array(AddViewPaths::class, $router->getMiddlewareGroups()['statamic.web']));
    }
}
