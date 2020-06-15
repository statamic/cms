<?php

namespace Tests\Composer;

use Facades\GuzzleHttp\Client;
use Facades\Statamic\Updater\Changelog;
use GuzzleHttp\Psr7\Response;
use Statamic\Statamic;
use Statamic\Updater\Changelog as RealChangelog;
use Tests\TestCase;

class ChangelogTest extends TestCase
{
    /** @test */
    public function it_can_get_statamic_changelog_from_core_slug()
    {
        Client::shouldReceive('get')
            ->andReturn($this->fakeCoreChangelogResponse(['3.0.2', '3.0.1', '3.0.0']));

        Changelog::swap(RealChangelog::product(Statamic::CORE_SLUG));
        Changelog::shouldReceive('currentVersion')->andReturn('3.0.1');
        Changelog::makePartial();

        $changelog = Changelog::get();

        $this->assertCount(3, $changelog);

        $this->assertEquals('3.0.2', $changelog[0]->version);
        $this->assertEquals('upgrade', $changelog[0]->type);
        $this->assertTrue($changelog[0]->latest);

        $this->assertEquals('3.0.1', $changelog[1]->version);
        $this->assertEquals('current', $changelog[1]->type);
        $this->assertFalse($changelog[1]->latest);

        $this->assertEquals('3.0.0', $changelog[2]->version);
        $this->assertEquals('downgrade', $changelog[2]->type);
        $this->assertFalse($changelog[2]->latest);

        collect($changelog)->each(function ($release) {
            $this->assertEquals('November 6th, 2018', $release->date);
            $this->assertContainsHtml($release->body);
        });
    }

    /** @test */
    public function it_can_get_addon_changelog_from_product_slug()
    {
        Client::shouldReceive('request')
            ->andReturn($this->fakeMarketplaceShowResponse(['1.0.2', '1.0.1', '1.0.0']));

        Changelog::swap(RealChangelog::product('deaths-tar-vulnerability'));
        Changelog::shouldReceive('currentVersion')->andReturn('1.0.1');
        Changelog::makePartial();

        $changelog = Changelog::get();

        $this->assertCount(3, $changelog);

        $this->assertEquals('1.0.2', $changelog[0]->version);
        $this->assertEquals('upgrade', $changelog[0]->type);
        $this->assertTrue($changelog[0]->latest);

        $this->assertEquals('1.0.1', $changelog[1]->version);
        $this->assertEquals('current', $changelog[1]->type);
        $this->assertFalse($changelog[1]->latest);

        $this->assertEquals('1.0.0', $changelog[2]->version);
        $this->assertEquals('downgrade', $changelog[2]->type);
        $this->assertFalse($changelog[2]->latest);

        collect($changelog)->each(function ($release) {
            $this->assertEquals('November 6th, 2018', $release->date);
            $this->assertContainsHtml($release->body);
        });
    }

    /** @test */
    public function it_can_get_latest_release()
    {
        Client::shouldReceive('request')
            ->andReturn($this->fakeMarketplaceShowResponse(['1.0.2', '1.0.1', '1.0.0']));

        Changelog::swap(RealChangelog::product('deaths-tar-vulnerability'));
        Changelog::shouldReceive('currentVersion')->andReturn('1.0.1');
        Changelog::makePartial();

        $latest = Changelog::latest();

        $this->assertEquals('1.0.2', $latest->version);
        $this->assertEquals('upgrade', $latest->type);
        $this->assertTrue($latest->latest);
    }

    private function fakeCoreChangelogResponse($versions)
    {
        return new Response(200, [], json_encode([
            'data' => $this->fakeReleasesData($versions),
        ]));
    }

    private function fakeMarketplaceShowResponse($versions)
    {
        return new Response(200, [], json_encode([
            'data' => [
                'variants' => [
                    ['releases' => $this->fakeReleasesData($versions)],
                ],
            ],
        ]));
    }

    private function fakeReleasesData($versions)
    {
        return collect($versions)->map(function ($version) {
            return [
                'version' => $version,
                'date' => '2018-11-06',
                'changelog' => '- [new] Stuff.',
            ];
        })->all();
    }
}
