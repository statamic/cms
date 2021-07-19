<?php

namespace Tests;

use Illuminate\Support\Facades\Route;
use Statamic\Facades\File;
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
                'dateTimeFormat' => Statamic::dateTimeFormat(),
                'cpDateFormat' => Statamic::cpDateFormat(),
                'cpDateTimeFormat' => Statamic::cpDateTimeFormat(),
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
    public function it_renders_svg_inline_by_default()
    {
        File::put(public_path('vendor/statamic/cp/svg/test.svg'), '<svg the totally real svg');

        $this->assertStringStartsWith('<svg ', Statamic::svg('test'));
    }

    /** @test */
    public function it_renders_svg_as_image_tag_with_icons_cdn_url_enabled()
    {
        config(['statamic.cp.icons_cdn_url' => 'http://cdn_url/']);

        $this->assertStringStartsWith('<img src="http://cdn_url/vendor/statamic/cp/svg/test.svg"', Statamic::svg('test'));
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

    /** @test */
    public function it_appends_time_if_system_date_format_doesnt_have_time_in_it()
    {
        config(['statamic.system.date_format' => 'Y--m--d']);

        $this->assertEquals('Y--m--d H:i', Statamic::dateTimeFormat());
    }

    /**
     * @test
     * @dataProvider formatsWithTime
     **/
    public function it_doesnt_append_time_if_system_date_format_already_has_time_in_it($format)
    {
        config(['statamic.system.date_format' => $format]);

        $this->assertEquals($format, Statamic::dateTimeFormat());
    }

    /** @test */
    public function it_appends_time_if_cp_date_format_doesnt_have_time_in_it()
    {
        config(['statamic.cp.date_format' => 'Y--m--d']);

        $this->assertEquals('Y--m--d H:i', Statamic::cpDateTimeFormat());
    }

    /**
     * @test
     * @dataProvider formatsWithTime
     **/
    public function it_doesnt_append_time_if_cp_date_format_already_has_time_in_it($format)
    {
        config(['statamic.cp.date_format' => $format]);

        $this->assertEquals($format, Statamic::cpDateTimeFormat());
    }

    public function formatsWithTime()
    {
        return [
            '12-hour without leading zeros' => ['g'],
            '24-hour without leading zeros' => ['G'],
            '12-hour with leading zeros' => ['h'],
            '24-hour with leading zeros' => ['H'],
            'unix timestamp' => ['U'],
            'ISO 8601' => ['c'],
            'RFC 2822' => ['r'],
        ];
    }
}
