<?php

namespace Tests\Composer;

use Facades\GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;

trait ChangelogTests
{
    abstract protected function changelog();

    #[Test]
    public function it_can_get_changelog_contents()
    {
        Client::shouldReceive('request')
            ->andReturn($this->fakeMarketplaceReleasesResponse(['2.0.0', '1.0.3', '1.0.2', '1.0.1', '1.0.0']));

        $changelog = $this->changelog();

        $contents = $changelog->get();

        $this->assertCount(5, $contents);
        $this->assertEquals(3, $changelog->availableUpdatesCount());
        $this->assertFalse($changelog->hasCriticalUpdate());

        $this->assertEquals('2.0.0', $contents[0]->version);
        $this->assertEquals('upgrade', $contents[0]->type);
        $this->assertTrue($contents[0]->latest);

        $this->assertEquals('1.0.3', $contents[1]->version);
        $this->assertEquals('upgrade', $contents[1]->type);
        $this->assertFalse($contents[1]->latest);

        $this->assertEquals('1.0.2', $contents[2]->version);
        $this->assertEquals('upgrade', $contents[2]->type);
        $this->assertFalse($contents[2]->latest);

        $this->assertEquals('1.0.1', $contents[3]->version);
        $this->assertEquals('current', $contents[3]->type);
        $this->assertFalse($contents[3]->latest);

        $this->assertEquals('1.0.0', $contents[4]->version);
        $this->assertEquals('downgrade', $contents[4]->type);
        $this->assertFalse($contents[4]->latest);

        collect($contents)->each(function ($release) {
            $this->assertEquals('2018-11-06T00:00:00+00:00', $release->date);
            $this->assertIsString($release->body);
            $this->assertFalse($release->critical);
        });
    }

    #[Test]
    public function it_can_get_latest_release()
    {
        Client::shouldReceive('request')
            ->andReturn($this->fakeMarketplaceReleasesResponse(['1.0.2', '1.0.1', '1.0.0']));

        $latest = $this->changelog()->latest();

        $this->assertEquals('1.0.2', $latest->version);
        $this->assertEquals('upgrade', $latest->type);
        $this->assertTrue($latest->latest);
        $this->assertFalse($latest->critical);
    }

    #[Test]
    public function it_exposes_critical_flag_from_marketplace()
    {
        Client::shouldReceive('request')
            ->andReturn($this->fakeMarketplaceReleasesResponse([
                ['version' => '2.0.0', 'critical' => true],
                ['version' => '1.0.3', 'critical' => false],
                '1.0.2',
                ['version' => '1.0.1', 'critical' => true],
                '1.0.0',
            ]));

        $contents = $this->changelog()->get();

        $this->assertSame([true, false, false, true, false], collect($contents)->map->critical->all());
        $this->assertTrue($this->changelog()->latest()->critical);
        $this->assertTrue($this->changelog()->hasCriticalUpdate());
    }

    #[Test]
    public function has_critical_update_false_when_no_upgrades_are_critical()
    {
        Client::shouldReceive('request')
            ->andReturn($this->fakeMarketplaceReleasesResponse([
                ['version' => '1.0.3', 'critical' => false],
                ['version' => '1.0.2', 'critical' => false],
                ['version' => '1.0.1', 'critical' => true], // current
                ['version' => '1.0.0', 'critical' => true],
            ]));

        $this->assertFalse($this->changelog()->hasCriticalUpdate());
    }

    #[Test]
    public function has_critical_update_true_when_upgrades_are_critical()
    {
        // An upgrade is marked as critical but intentionally not the latest.
        // This ensures that we are checking for ANY upgrades and not just the latest.

        Client::shouldReceive('request')
            ->andReturn($this->fakeMarketplaceReleasesResponse([
                ['version' => '1.0.3', 'critical' => false],
                ['version' => '1.0.2', 'critical' => true],
                ['version' => '1.0.1', 'critical' => false], // current
                ['version' => '1.0.0', 'critical' => false],
            ]));

        $this->assertTrue($this->changelog()->hasCriticalUpdate());
    }

    private function fakeCoreChangelogResponse($versions)
    {
        return new Response(200, [], json_encode([
            'data' => $this->fakeReleasesData($versions),
        ]));
    }

    private function fakeMarketplaceReleasesResponse($versions)
    {
        return new Response(200, [], json_encode([
            'data' => $this->fakeReleasesData($versions),
        ]));
    }

    private function fakeReleasesData($versions)
    {
        return collect($versions)->map(function ($release) {
            if (is_string($release)) {
                $release = ['version' => $release];
            }

            return [
                'version' => $release['version'],
                'date' => '2018-11-06',
                'changelog' => '- [new] Stuff.',
                'critical' => $release['critical'] ?? false,
            ];
        })->all();
    }
}
