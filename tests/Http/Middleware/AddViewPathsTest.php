<?php

namespace Tests\Http\Middleware;

use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Site;
use Statamic\Http\Middleware\AddViewPaths;
use Statamic\Support\Arr;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AddViewPathsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        mkdir(__DIR__.'/tmp/views/french', 0755, true);
        mkdir(__DIR__.'/tmp/other/views/english', 0755, true);
    }

    public function tearDown(): void
    {
        app('files')->deleteDirectory(__DIR__.'/tmp');

        parent::tearDown();
    }

    #[Test]
    #[DataProvider('viewPathProvider')]
    public function adds_view_paths($requestUrl, $expectedPaths)
    {
        $this->setSites([
            'english' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'french' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]);

        view()->getFinder()->setPaths($originalPaths = [
            __DIR__.'/tmp/views',
            __DIR__.'/tmp/other/views',
        ]);

        $this->setCurrentSiteBasedOnUrl($requestUrl);

        $request = $this->createRequest($requestUrl);
        $handled = false;

        (new AddViewPaths())->handle($request, function () use ($expectedPaths, &$handled) {
            $this->assertEquals($expectedPaths, view()->getFinder()->getPaths());
            $handled = true;

            return new Response;
        });

        $this->assertTrue($handled);
        $this->assertEquals($originalPaths, view()->getFinder()->getPaths());
    }

    #[Test]
    #[DataProvider('namespacedViewPathProvider')]
    public function adds_namespaced_view_paths($requestUrl, $expectedPaths)
    {
        $this->setSites([
            'english' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'french' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]);

        view()->getFinder()->replaceNamespace('foo', [
            __DIR__.'/tmp/views',
            __DIR__.'/tmp/other/views',
        ]);
        $originalHints = view()->getFinder()->getHints()['foo'];

        $this->setCurrentSiteBasedOnUrl($requestUrl);

        $request = $this->createRequest($requestUrl);
        $handled = false;

        (new AddViewPaths())->handle($request, function () use ($expectedPaths, &$handled) {
            $this->assertEquals($expectedPaths, Arr::get(view()->getFinder()->getHints(), 'foo'));
            $handled = true;

            return new Response;
        });

        $this->assertTrue($handled);
        $this->assertEquals($originalHints, Arr::get(view()->getFinder()->getHints(), 'foo'));
    }

    #[Test]
    public function adds_no_view_paths_on_single_site_when_site_directory_does_not_exist()
    {
        $this->setSites([
            'english' => ['url' => 'http://localhost/', 'locale' => 'en'],
        ]);

        view()->getFinder()->setPaths($originalPaths = [__DIR__.'/tmp/views']);
        view()->getFinder()->replaceNamespace('foo', $originalHints = [__DIR__.'/tmp/views']);

        $request = $this->createRequest('/test');
        $handled = false;

        (new AddViewPaths())->handle($request, function () use ($originalPaths, $originalHints, &$handled) {
            $this->assertEquals($originalPaths, view()->getFinder()->getPaths());
            $this->assertEquals($originalHints, Arr::get(view()->getFinder()->getHints(), 'foo'));
            $handled = true;

            return new Response;
        });

        $this->assertTrue($handled);
    }

    #[Test]
    public function adds_view_paths_on_single_site_when_site_directory_exists()
    {
        $this->setSites([
            'english' => ['url' => 'http://localhost/', 'locale' => 'en'],
        ]);

        view()->getFinder()->setPaths([__DIR__.'/tmp/other/views']);

        $request = $this->createRequest('/test');
        $handled = false;

        (new AddViewPaths())->handle($request, function () use (&$handled) {
            $this->assertEquals([
                __DIR__.'/tmp/other/views/english',
                __DIR__.'/tmp/other/views',
            ], view()->getFinder()->getPaths());
            $handled = true;

            return new Response;
        });

        $this->assertTrue($handled);
    }

    private function setCurrentSiteBasedOnUrl($requestUrl)
    {
        $site = Site::findByUrl('http://localhost'.$requestUrl);
        Site::setCurrent($site->handle());
    }

    private function createRequest($url)
    {
        $symfonyRequest = SymfonyRequest::create($url);
        $request = Request::createFromBase($symfonyRequest);
        app()->instance('request', $request);
    }

    public static function viewPathProvider()
    {
        return [
            'site directory exists in first path' => ['/fr/test', [
                __DIR__.'/tmp/views/french',
                __DIR__.'/tmp/views',
                __DIR__.'/tmp/other/views',
            ]],
            'site directory exists in second path' => ['/test', [
                __DIR__.'/tmp/views',
                __DIR__.'/tmp/other/views/english',
                __DIR__.'/tmp/other/views',
            ]],
        ];
    }

    public static function namespacedViewPathProvider()
    {
        return [
            'site directory exists in first path' => [
                '/fr/test',
                [
                    __DIR__.'/tmp/views/french',
                    __DIR__.'/tmp/views',
                    __DIR__.'/tmp/other/views',
                ],
            ],
            'site directory exists in second path' => [
                '/test',
                [
                    __DIR__.'/tmp/views',
                    __DIR__.'/tmp/other/views/english',
                    __DIR__.'/tmp/other/views',
                ],
            ],
        ];
    }

    #[Test]
    public function middleware_attached_to_routes()
    {
        /** @var Router $router */
        $router = app('router');
        $this->assertTrue(in_array(AddViewPaths::class, $router->getMiddlewareGroups()['statamic.web']));
    }
}
