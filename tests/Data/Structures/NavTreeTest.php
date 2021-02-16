<?php

namespace Tests\Data\Structures;

use Statamic\Facades\Blink;
use Statamic\Facades\Nav;
use Statamic\Facades\Site;
use Statamic\Structures\NavTree;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;
use Tests\UnlinksPaths;

class NavTreeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use UnlinksPaths;

    public function setUp(): void
    {
        parent::setUp();

        $stache = $this->app->make('stache');
        $stache->store('nav-trees')->directory($this->directory = '/path/to/structures/navigation');
    }

    /** @test */
    public function it_can_get_and_set_the_handle()
    {
        $tree = new NavTree;
        $this->assertNull($tree->handle());

        $return = $tree->handle('test');

        $this->assertSame($tree, $return);
        $this->assertEquals('test', $tree->handle());
    }

    /** @test */
    public function it_gets_the_structure()
    {
        $nav = Nav::make();
        Nav::shouldReceive('findByHandle')->with('test')->once()->andReturn($nav);

        $this->assertNull(Blink::get($blinkKey = 'nav-tree-structure-test'));

        $tree = (new NavTree)->handle('test');

        // Do it twice combined with the once() in the mock to show blink works.
        $this->assertSame($nav, $tree->structure());
        $this->assertSame($nav, $tree->structure());
        $this->assertSame($nav, Blink::get($blinkKey));
    }

    /** @test */
    public function it_gets_the_path()
    {
        $tree = Nav::make('links')->makeTree('en');
        $this->assertEquals('/path/to/structures/navigation/links.yaml', $tree->path());
    }

    /** @test */
    public function it_gets_the_path_when_using_multisite()
    {
        Site::setConfig(['sites' => [
            'one' => ['locale' => 'en_US', 'url' => '/one'],
            'two' => ['locale' => 'fr_Fr', 'url' => '/two'],
        ]]);
        $tree = Nav::make('links')->makeTree('en');
        $this->assertEquals('/path/to/structures/navigation/en/links.yaml', $tree->path());
    }
}
