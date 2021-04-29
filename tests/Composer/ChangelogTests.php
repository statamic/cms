<?php

namespace Tests\Composer;

use Facades\GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

trait ChangelogTests
{
    abstract protected function changelog();

    /** @test */
    public function it_can_get_changelog_contents()
    {
        config(['statamic.cp.date_format' => 'Y: F jS']);

        Client::shouldReceive('request')
            ->andReturn($this->fakeMarketplaceReleasesResponse(['2.0.0', '1.0.3', '1.0.2', '1.0.1', '1.0.0']));

        $changelog = $this->changelog();

        $contents = $changelog->get();

        $this->assertCount(5, $contents);
        $this->assertEquals(3, $changelog->availableUpdatesCount());

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
            $this->assertEquals('2018: November 6th', $release->date);
            $this->assertContainsHtml($release->body);
        });
    }

    /** @test */
    public function it_can_get_latest_release()
    {
        Client::shouldReceive('request')
            ->andReturn($this->fakeMarketplaceReleasesResponse(['1.0.2', '1.0.1', '1.0.0']));

        $latest = $this->changelog()->latest();

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

    private function fakeMarketplaceReleasesResponse($versions)
    {
        return new Response(200, [], json_encode([
            'data' => $this->fakeReleasesData($versions),
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
