<?php

namespace Tests;

use Illuminate\Contracts\Container\BindingResolutionException;
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
        $this->get('/not-cp');
        $this->assertFalse(Statamic::isCpRoute());

        $this->get('/cp');
        $this->assertTrue(Statamic::isCpRoute());

        $this->get('/cp/foo');
        $this->assertTrue(Statamic::isCpRoute());

        $this->get('/cpa');
        $this->assertFalse(Statamic::isCpRoute());

        config(['statamic.cp.enabled' => false]);
        $this->get('/cp/foo');
        $this->assertFalse(Statamic::isCpRoute());
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

    /** @test */
    public function it_wraps_fluent_tag_helper()
    {
        $this->assertInstanceOf(\Statamic\Tags\FluentTag::class, Statamic::tag('some_tag'));
    }

    /** @test */
    public function it_wraps_fluent_modifier_helper()
    {
        $this->assertInstanceOf(\Statamic\Modifiers\Modify::class, Statamic::modify('some_value'));
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

    /** @test */
    public function it_aliases_query_builders()
    {
        app()->bind('statamic.queries.test', function () {
            return 'the test query builder';
        });

        $this->assertEquals('the test query builder', Statamic::query('test'));
    }

    /** @test */
    public function native_query_builder_aliases_are_bound()
    {
        $aliases = [
            'entries' => \Statamic\Stache\Query\EntryQueryBuilder::class,
            'terms' => \Statamic\Stache\Query\TermQueryBuilder::class,
            'assets' => \Statamic\Assets\QueryBuilder::class,
            'users' => \Statamic\Stache\Query\UserQueryBuilder::class,
        ];

        foreach ($aliases as $alias => $class) {
            $this->assertInstanceOf($class, Statamic::query($alias));
        }
    }

    /** @test */
    public function it_throws_exception_for_invalid_query_builder_alias()
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('Target class [statamic.queries.test] does not exist.');

        Statamic::query('test');
    }
}
