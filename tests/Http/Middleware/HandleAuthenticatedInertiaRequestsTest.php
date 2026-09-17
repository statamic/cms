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

            Route::get('non-inertia-page-data-test', fn () => Statamic::nonInertiaPageData());
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
            ->actingAs(User::make()->makeSuper()->save())
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
    public function it_resolves_the_nav_for_pages_rendered_outside_of_inertia()
    {
        $data = $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get('/cp/non-inertia-page-data-test')
            ->assertOk()
            ->json();

        $this->assertNotEmpty($data['props']['_statamic']['nav']);
    }
}
