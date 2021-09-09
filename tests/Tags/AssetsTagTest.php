<?php

namespace Tests\Tags;

use Illuminate\Support\Facades\Storage;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\Parse;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AssetsTagTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__.'/tmp',
        ]]);

        Storage::fake('test');
        Storage::fake('dimensions-cache');
    }

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }

    /** @test */
    public function it_outputs_assets()
    {
        tap(Storage::fake('test'))->getDriver()->getConfig()->set('url', '/assets');

        Storage::disk('test')->put('a.txt', '');
        Storage::disk('test')->put('b.txt', '');

        AssetContainer::make('test')->disk('test')->save();

        $this->assertEquals(
            '123',
            $this->tag('{{ assets container="test" }}{{ total_results }}{{ /assets }}')
        );
    }
}
