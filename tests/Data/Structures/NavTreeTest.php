<?php

namespace Tests\Data\Structures;

use Facades\Statamic\Structures\BranchIds;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\NavTreeSaving;
use Statamic\Facades\Blink;
use Statamic\Facades\File;
use Statamic\Facades\Nav;
use Statamic\Facades\YAML;
use Statamic\Structures\NavTree;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;
use Tests\UnlinksPaths;

class NavTreeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use UnlinksPaths;

    private $directory;

    public function setUp(): void
    {
        parent::setUp();

        $stache = $this->app->make('stache');
        $stache->store('nav-trees')->directory($this->directory = $this->fakeStacheDirectory.$this->directory.'');
    }

    #[Test]
    public function it_can_get_and_set_the_handle()
    {
        $tree = new NavTree;
        $this->assertNull($tree->handle());

        $return = $tree->handle('test');

        $this->assertSame($tree, $return);
        $this->assertEquals('test', $tree->handle());
    }

    #[Test]
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

    #[Test]
    public function it_gets_the_path()
    {
        $tree = Nav::make('links')->makeTree('en');
        $this->assertEquals($this->directory.'/links.yaml', $tree->path());
    }

    #[Test]
    public function it_gets_the_path_when_using_multisite()
    {
        $this->setSites([
            'one' => ['locale' => 'en_US', 'url' => '/one'],
            'two' => ['locale' => 'fr_Fr', 'url' => '/two'],
        ]);
        $tree = Nav::make('links')->makeTree('en');
        $this->assertEquals($this->directory.'/en/links.yaml', $tree->path());
    }

    #[Test]
    public function it_can_ensure_ids_have_been_generated()
    {
        BranchIds::shouldReceive('ensure')
            ->with($existingTree = [['title' => 'Branch']])
            ->andReturn($updatedTree = [['id' => 'the-id', 'title' => 'Branch']])
            ->once();

        $nav = tap(Nav::make('links'))->save();
        $tree = tap($nav->makeTree('en', $existingTree))->save();

        $this->assertEquals(['tree' => $existingTree], YAML::file($tree->path())->parse());

        $return = $tree->ensureBranchIds();

        $this->assertEquals($tree, $return);
        $this->assertEquals($updatedTree, $tree->tree());
        $this->assertEquals(['tree' => $updatedTree], YAML::file($tree->path())->parse());
    }

    #[Test]
    public function it_doesnt_save_tree_when_ensuring_ids_if_nothing_changed()
    {
        BranchIds::shouldReceive('ensure')
            ->with($existingTree = [['id' => 'the-id', 'title' => 'Branch']])
            ->andReturn($existingTree)
            ->once();

        $nav = tap(Nav::make('links'))->save();
        $tree = tap($nav->makeTree('en', $existingTree))->save();

        File::put($tree->path(), $existingFileContents = 'different contents to show that it doesnt re-save');

        $return = $tree->ensureBranchIds();

        $this->assertEquals($tree, $return);
        $this->assertEquals($existingTree, $tree->tree());
        $this->assertEquals($existingFileContents, File::get($tree->path()));
    }

    #[Test]
    public function it_fires_a_saving_event()
    {
        Event::fake();

        $nav = tap(Nav::make('links'))->save();
        tap($nav->makeTree('en', [['id' => 'the-id', 'title' => 'Branch']]))->save();

        Event::assertDispatched(NavTreeSaving::class);
    }

    #[Test]
    public function returning_false_in_nav_tree_saving_stops_saving()
    {
        Event::listen(NavTreeSaving::class, function (NavTreeSaving $event) {
            return false;
        });

        $nav = tap(Nav::make('links'))->save();
        $tree = tap($nav->makeTree('en', [['id' => 'the-id', 'title' => 'Branch']]))->save();

        $this->assertFileDoesNotExist($tree->path());
    }
}
