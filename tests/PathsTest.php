<?php

namespace Tests;

use Statamic\Facades\Path;

class PathsTest extends TestCase
{
    public function testRelativePath()
    {
        $path = Path::makeRelative(base_path().'/content/foo/bar.md');
        $this->assertTrue($path == 'content/foo/bar.md');
    }

    public function testIfPage()
    {
        $this->assertTrue(Path::isPage('/foo/bar/index.md'));
        $this->assertTrue(Path::isPage('/foo/bar/fr.index.md'));
        $this->assertTrue(Path::isPage('/foo/1.bar/index.md'));
        $this->assertTrue(Path::isPage('/foo/1.bar/fr.index.md'));
        $this->assertTrue(Path::isPage('/foo/_1.bar/index.md'));
        $this->assertTrue(Path::isPage('/foo/_1.bar/fr.index.md'));
        $this->assertTrue(Path::isPage('/foo/__1.bar/index.md'));
        $this->assertTrue(Path::isPage('/foo/__1.bar/fr.index.md'));
        $this->assertFalse(Path::isPage('/foo/bar/2015-01-01.post.md'));
    }

    public function testIfEntry()
    {
        $this->assertFalse(Path::isEntry('/foo/bar/index.md'));
        $this->assertFalse(Path::isEntry('/foo/bar/fr.index.md'));
        $this->assertTrue(Path::isEntry('/foo/bar/2015-01-01.post.md'));
    }

    public function testHiddenPage()
    {
        $this->assertTrue(Path::isHidden('/foo/_bar/index.md'));
        $this->assertTrue(Path::isHidden('/foo/_1.bar/index.md'));
        $this->assertFalse(Path::isHidden('/_foo/bar/index.md'));
        $this->assertFalse(Path::isHidden('/foo/bar/index.md'));
    }

    public function testHiddenLocalizedPage()
    {
        $this->assertTrue(Path::isHidden('/foo/_bar/fr.index.md'));
        $this->assertFalse(Path::isHidden('/_foo/bar/fr.index.md'));
        $this->assertFalse(Path::isHidden('/foo/bar/fr.index.md'));
    }

    public function testHiddenEntry()
    {
        $this->assertTrue(Path::isHidden('/foo/bar/_2015-01-01.post.md'));
        $this->assertFalse(Path::isHidden('/foo/_bar/2015-01-01.post.md'));
        $this->assertFalse(Path::isHidden('/_foo/bar/2015-01-01.post.md'));
        $this->assertFalse(Path::isHidden('/foo/bar/2015-01-01.post.md'));
    }

    public function testDraftPage()
    {
        $this->assertTrue(Path::isDraft('/foo/__bar/index.md'));
        $this->assertTrue(Path::isDraft('/foo/__1.bar/index.md'));
        $this->assertFalse(Path::isDraft('/__foo/bar/index.md'));
        $this->assertFalse(Path::isDraft('/foo/bar/index.md'));
    }

    public function testDraftLocalizedPage()
    {
        $this->assertTrue(Path::isDraft('/foo/__bar/fr.index.md'));
        $this->assertTrue(Path::isDraft('/foo/__1.bar/fr.index.md'));
        $this->assertFalse(Path::isDraft('/__foo/bar/fr.index.md'));
        $this->assertFalse(Path::isDraft('/foo/bar/fr.index.md'));
    }

    public function testDraftEntry()
    {
        $this->assertTrue(Path::isDraft('/foo/bar/__2015-01-01.post.md'));
        $this->assertFalse(Path::isDraft('/foo/__bar/2015-01-01.post.md'));
        $this->assertFalse(Path::isDraft('/__foo/bar/2015-01-01.post.md'));
        $this->assertFalse(Path::isDraft('/foo/bar/2015-01-01.post.md'));
    }

    public function testAbsolutePaths()
    {
        $this->assertTrue(Path::isAbsolute('/Users/foo/bar'));
        $this->assertTrue(Path::isAbsolute('C:\Windows\System'));
        $this->assertFalse(Path::isAbsolute('path/to/something'));
    }

    public function testResolvesPaths()
    {
        $this->assertEquals(
            '/Users/joe/Sites/website/site/content/pages/index.md',
            Path::resolve('/Users/joe/Sites/website/statamic/../site/content/pages/index.md')
        );
    }

    public function testResolvesWindowsPaths()
    {
        $this->assertEquals(
            'C:/Users/joe/Sites/website/site/content/pages/index.md',
            Path::resolve('C:\Users\joe\Sites\website\statamic\..\site\content\pages\index.md')
        );
    }
}
