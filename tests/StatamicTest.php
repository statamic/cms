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

        Route::get('cp/date-format', function () {
            return [
                'dateFormat' => Statamic::dateFormat(),
                'cpDateFormat' => Statamic::cpDateFormat(),
            ];
        });
    }

    /** @test */
    public function it_checks_for_cp_route()
    {
        $this->assertFalse($this->getJson('/is-cp-route')->assertOk()['isCpRoute']);

        $this->assertTrue($this->getJson('/cp/is-cp-route')->assertOk()['isCpRoute']);
    }

    /** @test */
    public function outside_of_cp_the_date_format_is_the_system()
    {
        $response = $this->getJson('/date-format')->assertOk();

        $this->assertEquals('system-date-format', $response['dateFormat']);
    }

    /** @test */
    public function outside_of_cp_it_gets_cp_date_format()
    {
        $response = $this->getJson('/date-format')->assertOk();

        $this->assertEquals('cp-date-format', $response['cpDateFormat']);
    }

    /** @test */
    public function outside_of_cp_it_gets_users_preferred_date_format_when_requesting_default_format()
    {
        $user = tap(User::make())->save();
        $user->setPreference('date_format', 'user-date-format');

        $response = $this->actingAs($user)->getJson('/date-format')->assertOk();

        $this->assertEquals('user-date-format', $response['dateFormat']);
    }

    /** @test */
    public function outside_of_cp_it_gets_users_preferred_date_format_when_requesting_cp_format()
    {
        $user = tap(User::make())->save();
        $user->setPreference('date_format', 'user-date-format');

        $response = $this->actingAs($user)->getJson('/date-format')->assertOk();

        $this->assertEquals('user-date-format', $response['cpDateFormat']);
    }

    /** @test */
    public function inside_cp_the_date_format_is_the_cp_format()
    {
        $response = $this->getJson('/cp/date-format')->assertOk();

        $this->assertEquals('cp-date-format', $response['dateFormat']);
    }

    /** @test */
    public function inside_cp_it_gets_the_cp_date_format()
    {
        $response = $this->getJson('/cp/date-format')->assertOk();

        $this->assertEquals('cp-date-format', $response['cpDateFormat']);
    }

    /** @test */
    public function inside_the_cp_it_gets_the_users_preferred_date_format_when_requesting_default_format()
    {
        $user = tap(User::make())->save();
        $user->setPreference('date_format', 'user-date-format');

        $response = $this->actingAs($user)->getJson('/cp/date-format')->assertOk();

        $this->assertEquals('user-date-format', $response['dateFormat']);
    }

    /** @test */
    public function inside_the_cp_it_gets_the_users_preferred_date_format_when_requesting_cp_format()
    {
        $user = tap(User::make())->save();
        $user->setPreference('date_format', 'user-date-format');

        $response = $this->actingAs($user)->getJson('/cp/date-format')->assertOk();

        $this->assertEquals('user-date-format', $response['cpDateFormat']);
    }
}
