<?php

namespace Tests\Http\Middleware;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Statamic\Statamic;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class HandleInertiaRequestsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        Statamic::pushCpRoutes(function () {
            Route::get('streamed-download-test', function () {
                return response()->streamDownload(fn () => print 'id,name', 'data.csv');
            });

            Route::get('file-download-test', function () {
                return response()->download(__FILE__);
            });

            Route::get('empty-response-test', function () {
                return response()->noContent(200);
            });
        });
    }

    #[Test]
    public function it_converts_streamed_downloads_into_location_visits_for_inertia_requests()
    {
        // Inertia requests are made over XHR, which can't trigger downloads. Returning
        // 409 + X-Inertia-Location makes the client do a full browser visit instead,
        // which lets the browser handle the download natively.

        $this
            ->actingAs(User::make()->makeSuper())
            ->get('/cp/streamed-download-test', ['X-Inertia' => 'true'])
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', 'http://localhost/cp/streamed-download-test');
    }

    #[Test]
    public function it_converts_file_downloads_into_location_visits_for_inertia_requests()
    {
        $this
            ->actingAs(User::make()->makeSuper())
            ->get('/cp/file-download-test', ['X-Inertia' => 'true'])
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', 'http://localhost/cp/file-download-test');
    }

    #[Test]
    public function it_leaves_downloads_untouched_for_non_inertia_requests()
    {
        $this
            ->actingAs(User::make()->makeSuper())
            ->get('/cp/streamed-download-test')
            ->assertOk()
            ->assertDownload('data.csv');
    }

    #[Test]
    public function it_redirects_back_for_empty_inertia_responses()
    {
        $this
            ->actingAs(User::make()->makeSuper())
            ->from('/cp/utilities')
            ->get('/cp/empty-response-test', ['X-Inertia' => 'true'])
            ->assertRedirect('/cp/utilities');
    }
}
