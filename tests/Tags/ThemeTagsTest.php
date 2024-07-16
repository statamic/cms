<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\File;
use Statamic\Facades\Parse;
use Statamic\Facades\Path;
use Tests\TestCase;

class ThemeTagsTest extends TestCase
{
    protected $path;

    public function setUp(): void
    {
        $this->path = '';

        parent::setUp();
    }

    private function tag($tag): string
    {
        return Parse::template($tag, []);
    }

    public function testOutputsThemedJs()
    {
        $this->assertEquals(
            $this->path.'/js/app.js',
            $this->tag('{{ theme:js }}')
        );
    }

    public function testOutputsNamedJs()
    {
        $this->assertEquals(
            $this->path.'/js/script.js',
            $this->tag('{{ theme:js src="script.js" }}')
        );
    }

    public function testOutputsNamedJsWithAppendedExtension()
    {
        $this->assertEquals(
            $this->path.'/js/script.js',
            $this->tag('{{ theme:js src="script" }}')
        );
    }

    public function testOutputsJsTag()
    {
        $this->assertEquals(
            '<script src="'.$this->path.'/js/script.js"></script>',
            $this->tag('{{ theme:js src="script" tag="true" }}')
        );
    }

    public function testOutputsThemedCss()
    {
        $this->assertEquals(
            $this->path.'/css/app.css',
            $this->tag('{{ theme:css }}')
        );
    }

    public function testOutputsNamedCss()
    {
        $this->assertEquals(
            $this->path.'/css/style.css',
            $this->tag('{{ theme:css src="style.css" }}')
        );
    }

    public function testOutputsNamedCssWithAppendedExtension()
    {
        $this->assertEquals(
            $this->path.'/css/style.css',
            $this->tag('{{ theme:css src="style" }}')
        );
    }

    public function testOutputsCssTag()
    {
        $this->assertEquals(
            '<link rel="stylesheet" href="'.$this->path.'/css/style.css" />',
            $this->tag('{{ theme:css src="style" tag="true" }}')
        );
    }

    public function testOutputsAssetPath()
    {
        $this->assertEquals(
            $this->path.'/img/hat.jpg',
            $this->tag('{{ theme:asset src="img/hat.jpg" }}')
        );
    }

    public function testOutputsAssetPathAndDoesntAppendExtension()
    {
        $this->assertEquals(
            $this->path.'/img/hat',
            $this->tag('{{ theme:asset src="img/hat" }}')
        );
    }

    public function testOutputsAssetPathDynamically()
    {
        $this->assertEquals(
            $this->path.'/img/hat.jpg',
            $this->tag('{{ theme:img src="hat.jpg" }}')
        );
    }

    public function testOutputsFileContents()
    {
        File::shouldReceive('disk')->andReturn($disk = \Mockery::mock());
        $disk->shouldReceive('exists')->with('test.txt')->once()->andReturnTrue();
        $disk->shouldReceive('get')->with('test.txt')->andReturn('contents');
        $disk->shouldReceive('isWithinRoot')->with('test.txt')->andReturnTrue();

        $this->assertEquals(
            'contents',
            $this->tag('{{ theme:output src="test.txt" }}')
        );
    }

    public function testDoesntOutputFileContentsIfFileDoesntExist()
    {
        File::shouldReceive('disk')->andReturn($disk = \Mockery::mock());
        $disk->shouldReceive('exists')->with('test.txt')->once()->andReturnFalse();

        $this->assertEquals(
            '',
            $this->tag('{{ theme:output src="test.txt" }}')
        );
    }

    public function testDoesntOutputFileContentsIfOutsideOfResources()
    {
        File::shouldReceive('disk')->andReturn($disk = \Mockery::mock());
        $disk->shouldReceive('exists')->with('test.txt')->once()->andReturnTrue();
        $disk->shouldReceive('isWithinRoot')->with('test.txt')->andReturnFalse();

        $this->assertEquals(
            '',
            $this->tag('{{ theme:output src="test.txt" }}')
        );
    }

    public function testAppendsTimestampForCacheBusting()
    {
        File::shouldReceive('exists')->with(Path::tidy(public_path('/js/foo.js')))->andReturnTrue();

        File::shouldReceive('lastModified')
            ->withArgs(function ($arg) {
                return Path::tidy(public_path('/js/foo.js')) === Path::tidy($arg);
            })
            ->andReturn('12345');

        $this->assertEquals(
            '/js/foo.js?v=12345',
            $this->tag('{{ theme:js src="foo" cache_bust="true" }}')
        );
    }

    #[Test]
    public function gets_versioned_filename_for_mix()
    {
        File::shouldReceive('get')
            ->with(public_path('mix-manifest.json'))
            ->andReturn('{"/js/foo.js": "/js/foo.js?id=12345"}');

        $this->assertEquals(
            '/js/foo.js?id=12345',
            $this->tag('{{ theme:js src="foo" version="true" }}')
        );
    }

    #[Test]
    public function gets_versioned_filename_for_elixir()
    {
        File::shouldReceive('get')
            ->with(public_path('mix-manifest.json'))
            ->andReturnNull();

        File::shouldReceive('get')
            ->with(public_path('build/rev-manifest.json'))
            ->andReturn('{"js/foo.js": "js/foo-12345.js"}');

        $this->assertEquals(
            '/build/js/foo-12345.js',
            $this->tag('{{ theme:js src="foo" version="true" }}')
        );
    }

    #[Test]
    public function gets_regular_filename_if_file_isnt_in_mix_manifest()
    {
        File::shouldReceive('get')
            ->with(public_path('mix-manifest.json'))
            ->andReturn('{"/js/foo.js": "/js/foo.js?id=12345"}');

        $this->assertEquals(
            '/js/non-versioned-file.js',
            $this->tag('{{ theme:js src="non-versioned-file" version="true" }}')
        );
    }

    #[Test]
    public function gets_regular_filename_if_file_isnt_in_elixir_manifest()
    {
        File::shouldReceive('get')
            ->with(public_path('mix-manifest.json'))
            ->andReturnNull();

        File::shouldReceive('get')
            ->with(public_path('build/rev-manifest.json'))
            ->andReturn('{"js/foo.js": "js/foo-12345.js"}');

        $this->assertEquals(
            '/js/non-versioned-file.js',
            $this->tag('{{ theme:js src="non-versioned-file" version="true" }}')
        );
    }

    #[Test]
    public function gets_regular_filename_if_manifests_dont_exist()
    {
        File::shouldReceive('get')
            ->with(public_path('mix-manifest.json'))
            ->andReturnNull();

        File::shouldReceive('get')
            ->with(public_path('build/rev-manifest.json'))
            ->andReturnNull();

        $this->assertEquals(
            '/js/foo.js',
            $this->tag('{{ theme:js src="foo" version="true" }}')
        );
    }
}
