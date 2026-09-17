<?php

namespace Tests\Http\Middleware;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\User;
use Statamic\Statamic;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class HandleAuthenticatedInertiaRequestsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected $shouldPreventNavBeingBuilt = false;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        Statamic::pushCpRoutes(function () {
            Route::get('json-response-test', fn () => ['foo' => 'bar']);

            Route::get('blade-page-test', fn () => view('statamic::layout'));
        });
    }

    #[Test]
    public function it_doesnt_build_the_nav_for_responses_that_arent_inertia_pages()
    {
        $built = false;

        Nav::extend(function () use (&$built) {
            $built = true;
        });

        $this
            ->actingAs(User::make()->makeSuper())
            ->get('/cp/json-response-test')
            ->assertOk();

        $this->assertFalse($built);
    }

    #[Test]
    public function it_builds_the_nav_for_inertia_pages()
    {
        $built = false;

        Nav::extend(function () use (&$built) {
            $built = true;
        });

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get('/cp/dashboard')
            ->assertOk();

        $this->assertTrue($built);
    }

    #[Test]
    public function it_builds_the_nav_for_blade_based_pages()
    {
        $response = $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get('/cp/blade-page-test')
            ->assertOk();

        preg_match('/data-page="([^"]*)"/', $response->getContent(), $matches);

        $page = json_decode(htmlspecialchars_decode($matches[1], ENT_QUOTES), true);

        $this->assertNotEmpty($page['props']['_statamic']['nav']);
    }
}
