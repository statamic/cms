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
            'a.txtb.txt',
            $this->tag('{{ assets container="test" }}{{ title }}{{ /assets }}')
        );
    }

    /** @test */
    public function it_paginates_assets()
    {
        $antlers = <<<'ANTLERS'
{{ assets container="test" limit="2" paginate="true" as="pics" }}
{{ pics }}{{ title }}{{ /pics }}
{{ paginate }}
{{ prev_page }} is empty
{{ current_page }} of {{ total_pages }} pages
Total items: {{ total_items }}
{{ next_page }}
{{ /paginate }}
{{ /assets }}
ANTLERS;
        $expect = <<<'EXPECTATION'
a.txtb.txt
 is empty
1 of 3 pages
Total items: 6
http://localhost?page=2


EXPECTATION;

        tap(Storage::fake('test'))->getDriver()->getConfig()->set('url', '/assets');

        Storage::disk('test')->put('a.txt', '');
        Storage::disk('test')->put('b.txt', '');
        Storage::disk('test')->put('c.txt', '');
        Storage::disk('test')->put('d.txt', '');
        Storage::disk('test')->put('e.txt', '');
        Storage::disk('test')->put('f.txt', '');

        AssetContainer::make('test')->disk('test')->save();

        $this->assertEquals(
            $expect,
            $this->tag($antlers)
        );
    }
}
