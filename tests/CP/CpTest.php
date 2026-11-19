<?php

namespace Tests\CP;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Statamic\Statamic;
use Tests\TestCase;

class CpTest extends TestCase
{
    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        Statamic::pushCpRoutes(function () {
            Route::get('/date-test', function () {
                return (string) Date::now();
            });
        });
    }

    #[Test]
    public function it_sets_the_carbon_to_string_format()
    {
        Date::setTestNow(
            Date::parse('December 25st, 2022 10:32pm', 'America/New_York')
        );

        $this->assertDefaultCarbonFormat();

        $this
            ->actingAs(User::make()->makeSuper())
            ->get('/cp/date-test')
            ->assertSee('2022-12-26T03:32:00+00:00');

        $this->assertDefaultCarbonFormat();
    }

    private function assertDefaultCarbonFormat()
    {
        $this->assertEquals(
            Date::now()->format(Carbon::DEFAULT_TO_STRING_FORMAT),
            (string) Date::now(),
            'Carbon was not formatted using the default format.'
        );
    }
}
