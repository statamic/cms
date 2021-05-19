<?php

namespace Tests;

use Illuminate\Support\Facades\Route;
use Statamic\Facades\User;
use Statamic\Statamic;

class StatamicTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('statamic.cp.date_format', 'cp-date-format');
        $app['config']->set('statamic.system.date_format', 'system-date-format');

        Route::get('is-cp-route', function () {
            return ['isCpRoute' => Statamic::isCpRoute()];
        });

        Route::get('cp/is-cp-route', function () {
            return ['isCpRoute' => Statamic::isCpRoute()];
        });

        Route::get('date-format', function () {
            return [
                'dateFormat' => Statamic::dateFormat(),
                'cpDateFormat' => Statamic::cpDateFormat(),
            ];
        });
    }

    /** @test */
    public function it_checks_for_cp_route()
    {
        $this->assertFalse($this->getJson('/is-cp-route')->assertOk()->json('isCpRoute'));

        $this->assertTrue($this->getJson('/cp/is-cp-route')->assertOk()->json('isCpRoute'));
    }

    /** @test */
    public function it_gets_the_system_date_format()
    {
        $this->assertEquals('system-date-format', Statamic::dateFormat());
    }

    /** @test */
    public function it_gets_the_cp_date_format()
    {
        $this->assertEquals('cp-date-format', Statamic::cpDateFormat());
    }

    /** @test */
    public function it_gets_the_users_preferred_date_format_when_requesting_cp_format_but_not_the_system_format()
    {
        $user = tap(User::make())->save();
        $user->setPreference('date_format', 'user-date-format');

        $response = $this->actingAs($user)->getJson('/date-format')->assertOk();

        $this->assertEquals('user-date-format', $response->json('cpDateFormat'));
        $this->assertEquals('system-date-format', $response->json('dateFormat'));
    }
}
