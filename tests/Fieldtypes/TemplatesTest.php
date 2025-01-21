<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\File;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TemplatesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private string $dir;

    public function setUp(): void
    {
        parent::setUp();

        app('files')->makeDirectory($this->dir = __DIR__.'/templates-test-tmp', force: true);

        $this->app['config']->set('view.paths', [$this->dir.'/views']);
    }

    public function tearDown(): void
    {
        app('files')->deleteDirectory($this->dir);

        parent::tearDown();
    }

    #[Test]
    public function it_returns_a_list_of_templates()
    {
        $files = [
            // Regular files, these should all be shown.
            'alfa.html',
            'one/bravo.html',
            'one/two/charlie.html',
            'one/two/delta.html',

            // .git directories at any level should get filtered out
            '.git/echo.html',
            'one/.git/foxtrot.html',
            'one/two/.git/golf.html',

            // node_modules at any level should get filtered out
            'node_modules/hotel.html',
            'one/node_modules/india.html',
            'one/two/node_modules/juliett.html',

            // dot directories at any level should get filtered out
            '.kilo/lima.html',
            'one/.mike/november.html',
            'one/two/.oscar/papa.html',

            // dotfiles at any level should get filtered out
            '.quebec.html',
            'one/.rome.html',
            'one/two/.sierra.html',
        ];

        foreach ($files as $path) {
            File::put($this->dir.'/views/'.$path, '');
        }

        // Empty directories should be ignored.
        File::makeDirectory($this->dir.'/views/empty');

        // Empty symlinked directories should be ignored.
        File::makeDirectory($this->dir.'/empty-symlink-target');
        app('files')->link($this->dir.'/empty-symlink-target', $this->dir.'/views/empty-symlink');

        // Files in symlinked directories should be shown.
        File::put($this->dir.'/symlink-target-dir/tango.html', '');
        File::put($this->dir.'/symlink-target-dir/three/uniform.html', '');
        app('files')->link($this->dir.'/symlink-target-dir', $this->dir.'/views/symlink-dir');

        // Symlinked files should be shown.
        File::put($this->dir.'/foo.html', '');
        app('files')->link($this->dir.'/foo.html', $this->dir.'/views/victor.html');

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('api.templates.index'))
            ->assertJson([
                'alfa',
                'one/bravo',
                'one/two/charlie',
                'one/two/delta',
                'symlink-dir/tango',
                'symlink-dir/three/uniform',
                'victor',
            ]);
    }
}
