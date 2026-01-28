<?php

namespace Tests\Jobs;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Jobs\ReportThemeUsage;
use Statamic\Licensing\Outpost;
use Tests\TestCase;

class ReportThemeUsageTest extends TestCase
{
    #[Test]
    #[DataProvider('reportProvider')]
    public function it_reports_to_outpost($oldTheme, $newTheme, $expectedTheme)
    {
        Http::preventStrayRequests();
        Http::fake();

        $outpost = Mockery::mock(Outpost::class);
        $outpost->shouldReceive('usingLicenseKeyFile')->andReturnFalse();

        $job = new ReportThemeUsage($oldTheme, $newTheme);

        $job->handle($outpost);

        if ($expectedTheme === null) {
            Http::assertNothingSent();
        } else {
            Http::assertSent(function (Request $request) use ($expectedTheme) {
                return
                    $request->url() === 'https://outpost.statamic.com/v3/theme' &&
                    $request->data() === ['theme' => $expectedTheme];
            });
        }
    }

    public static function reportProvider()
    {
        return [
            'null to null' => [null, null, null],
            'null to theme id int' => [null, ['id' => 1], 1],
            'null to theme id string' => [null, ['id' => 'alfa'], null],
            'null to theme array with no id' => [null, ['foo' => 'bar'], null],
            'null to string' => [null, 'dark', null],
            'theme int to theme int' => [['id' => 1], ['id' => 2], 2],
            'theme int to theme string' => [['id' => 1], ['id' => 'alfa'], null],
            'same theme int' => [['id' => 1], ['id' => 1], null],
            'theme int to null' => [['id' => 1], null, null],
            'theme int to theme array with no id' => [['id' => 1], ['foo' => 'bar'], null],
            'theme int to string' => [['id' => 1], 'dark', null],
            'string to theme int' => ['light', ['id' => 2], 2],
            'string to theme string' => ['light', ['id' => 'alfa'], null],
            'string to null' => ['light', null, null],
            'string to theme array with no id' => ['light', ['foo' => 'bar'], null],
            'string to string' => ['light', 'dark', null],
        ];
    }

    #[Test]
    public function it_doesnt_report_if_using_license_key_file()
    {
        Http::preventStrayRequests();
        Http::fake();

        $outpost = Mockery::mock(Outpost::class);
        $outpost->shouldReceive('usingLicenseKeyFile')->andReturnTrue();

        $job = new ReportThemeUsage(null, ['id' => 1]);

        $job->handle($outpost);

        Http::assertNothingSent();
    }
}
