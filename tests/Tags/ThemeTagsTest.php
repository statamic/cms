<?php

namespace Statamic\Tests\Tags;

use Statamic\API\File;
use Tests\TestCase;
use Statamic\API\Parse;

class ThemeTagsTest extends TestCase
{
    protected $path;

    public function setUp()
    {
        $this->path = '';

        parent::setUp();
    }

    private function tag($tag)
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
        $contents = File::get('site/themes/redwood/package.json');

        $this->assertEquals(
            $contents,
            $this->tag('{{ theme:output src="package.json" }}')
        );
    }

    public function testAppendsTimestampForCacheBusting()
    {
        File::shouldReceive('lastModified')
            ->with(public_path('/js/foo.js'))
            ->andReturn('12345');

        $this->assertEquals(
            '/js/foo.js?v=12345',
            $this->tag('{{ theme:js src="foo" cache_bust="true" }}')
        );
    }
}
