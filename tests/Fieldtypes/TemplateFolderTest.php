<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\File;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\TemplateFolder;
use Tests\TestCase;

class TemplateFolderTest extends TestCase
{
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
    public function it_returns_a_list_of_directories()
    {
        $this->createFiles();

        $fieldtype = $this->fieldtype();

        $items = $fieldtype->getIndexItems(request());

        // A collection with identical id/title keys are returned but we're only really concerned about the content.
        $actual = $items->map->id->all();

        $this->assertEquals([
            'empty',
            'empty-symlink',
            'empty-symlink/three',
            'one',
            'one/two',
            'symlink-dir',
            'symlink-dir/five',
            'symlink-dir/four',
        ], $actual);
    }

    private function createFiles()
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

            // dotfiles at any level should get filtered out
            '.kilo.html',
            'one/.lima.html',
            'one/two/.mike.html',
        ];

        foreach ($files as $path) {
            File::put($this->dir.'/views/'.$path, '');
        }

        // Empty directories should also be shown.
        File::makeDirectory($this->dir.'/views/empty');

        // Symlinked directories (even empties) should be shown.
        File::makeDirectory($this->dir.'/empty-symlink-target');
        File::makeDirectory($this->dir.'/empty-symlink-target/three');
        File::put($this->dir.'/symlink-target-dir/tango.html', '');
        File::put($this->dir.'/symlink-target-dir/four/uniform.html', '');
        File::makeDirectory($this->dir.'/symlink-target-dir/five');
        symlink($this->dir.'/empty-symlink-target', $this->dir.'/views/empty-symlink');
        symlink($this->dir.'/symlink-target-dir', $this->dir.'/views/symlink-dir');

        // Symlinked files should not.
        File::put($this->dir.'/foo.html', '');
        symlink($this->dir.'/foo.html', $this->dir.'/views/victor.html');
    }

    private function fieldtype()
    {
        $field = new Field('test', array_merge([
            'type' => 'template_folder',
        ]));

        return (new TemplateFolder)->setField($field);
    }
}
