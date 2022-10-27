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
     * @dataProvider viewPathProvider
     */
    public function adds_view_paths($isAmpEnabled, $requestUrl, $expectedPaths)
    {
        Site::setConfig(['sites' => [
            'english' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'french' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]]);

        view()->getFinder()->setPaths([
            '/path/to/views',
            '/path/to/other/views',
        ]);

        config(['statamic.amp.enabled' => $isAmpEnabled]);

        $this->setCurrentSiteBasedOnUrl($requestUrl);

        $request = $this->createRequest($requestUrl);

        (new AddViewPaths())->handle($request, fn () => new Response());

        $this->assertEquals($expectedPaths, view()->getFinder()->getPaths());
    }

    /**
     * @test
     * @dataProvider namespacedViewPathProvider
     */
    public function adds_namespaced_view_paths($requestUrl, $expectedPaths)
    {
        Site::setConfig(['sites' => [
            'english' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'french' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]]);

        view()->getFinder()->replaceNamespace('foo', '/path/to/views');

        $this->setCurrentSiteBasedOnUrl($requestUrl);

        $request = $this->createRequest($requestUrl);

        (new AddViewPaths())->handle($request, fn () => new Response());

        $this->assertEquals($expectedPaths, array_get(view()->getFinder()->getHints(), 'foo'));
    }

    private function setCurrentSiteBasedOnUrl($requestUrl)
    {
        $url = 'http://localhost'.Str::removeLeft($requestUrl, '/amp');
        $site = Site::findByUrl($url);
        Site::setCurrent($site->handle());
    }

    private function createRequest($url)
    {
        $symfonyRequest = SymfonyRequest::create($url);
        $request = Request::createFromBase($symfonyRequest);
        app()->instance('request', $request);
    }

    public function viewPathProvider()
    {
        return [
            'amp enabled, amp request' => [true, '/amp/fr/test', [
                '/path/to/views/french',
                '/path/to/views',
                '/path/to/other/views/french',
                '/path/to/other/views',
            ]],
            'amp enabled, non-amp request' => [true, '/fr/test', [
                '/path/to/views/french',
                '/path/to/views',
                '/path/to/other/views/french',
                '/path/to/other/views',
            ]],
            'amp disabled, default site' => [false, '/test', [
                '/path/to/views/english',
                '/path/to/views',
                '/path/to/other/views/english',
                '/path/to/other/views',
            ]],
            'amp disabled, second site' => [false, '/fr/test', [
                '/path/to/views/french',
                '/path/to/views',
                '/path/to/other/views/french',
                '/path/to/other/views',
            ]],
        ];
    }

    public function namespacedViewPathProvider()
    {
        return [
            'default site' => [
                '/test',
                [
                    '/path/to/views/english',
                    '/path/to/views',
                ],
            ],
            'second site' => [
                '/fr/test',
                [
                    '/path/to/views/french',
                    '/path/to/views',
                ],
            ],
        ];
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
