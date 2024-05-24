<?php

namespace Tests\Fieldtypes;

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

        app('files')->makeDirectory($this->dir = __DIR__.'/templates-test-tmp');

        $this->app['config']->set('view.paths', [$this->dir]);
    }

    public function tearDown(): void
    {
        app('files')->deleteDirectory($this->dir);

        parent::tearDown();
    }

    /** @test */
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
            File::put($this->dir.'/'.$path, '');
        }

        // Empty directories should be ignored.
        File::makeDirectory($this->dir.'/empty');

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('api.templates.index'))
            ->assertJson([
                'alfa',
                'one/bravo',
                'one/two/charlie',
                'one/two/delta',
            ]);
    }
}
