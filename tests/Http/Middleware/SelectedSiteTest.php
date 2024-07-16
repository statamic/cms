<?php

namespace Tests\Http\Middleware;

use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\Middleware\CP\SelectedSite;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SelectedSiteTest extends TestCase
{
    use FakesRoles, PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_sets_selected_site_first_authorized_one()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr'],
            'de' => ['url' => '/de/', 'locale' => 'de'],
        ]);

        Site::setSelected('de');
        $this->assertEquals('de', Site::selected()->handle());

        $this->setTestRoles(['test' => ['access fr site']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this->actingAs($user);
        $request = $this->createRequest('/cp/foo');
        $handled = false;

        (new SelectedSite())->handle($request, function () use (&$handled) {
            $handled = true;

            return new Response;
        });

        $this->assertTrue($handled);
        $this->assertEquals('fr', Site::selected()->handle());
    }

    #[Test]
    public function it_doesnt_do_anything_when_there_are_no_authorized_sites()
    {
        // If the user doesn't have permission to access any sites, then... ¯\_(ツ)_/¯
        // The global site selector isn't going to be visible, and they won't be
        // able to able to access any areas that require a site anyway.

        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr'],
            'de' => ['url' => '/de/', 'locale' => 'de'],
        ]);

        Site::setSelected('de');
        $this->assertEquals('de', Site::selected()->handle());

        $this->setTestRoles(['test' => [
            // no authorized sites
        ]]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this->actingAs($user);
        $request = $this->createRequest('/cp/foo');
        $handled = false;

        (new SelectedSite())->handle($request, function () use (&$handled) {
            $handled = true;

            return new Response;
        });

        $this->assertTrue($handled);
        $this->assertEquals('de', Site::selected()->handle());
    }

    #[Test]
    public function middleware_attached_to_routes()
    {
        /** @var Router $router */
        $router = app('router');
        $this->assertTrue(in_array(SelectedSite::class, $router->getMiddlewareGroups()['statamic.cp.authenticated']));
    }

    private function createRequest($url)
    {
        $symfonyRequest = SymfonyRequest::create($url);
        $request = Request::createFromBase($symfonyRequest);
        app()->instance('request', $request);

        return $request;
    }
}
