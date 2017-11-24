<?php

namespace Tests;

use Statamic\Data\Content\PathBuilder;

class PathBuilderTest extends TestCase
{
    /**
     * @var \Statamic\Data\Content\PathBuilder
     */
    protected $builder;

    public function setUp()
    {
        parent::setUp();

        $this->builder = new PathBuilder;
    }

    public function testBuildsSimplePagePath()
    {
        $path = $this->builder->page()->uri('/about')->get();

        $this->assertEquals('pages/about/index.md', $path);
    }

    public function testBuildsSimpleLocalizedPagePath()
    {
        $path = $this->builder->page()->uri('/about')->locale('fr')->get();

        $this->assertEquals('pages/about/fr.index.md', $path);
    }

    public function testBuildsSimplePagePathWithDefaultLocale()
    {
        $path = $this->builder->page()->uri('/about')->locale('en')->get();

        $this->assertEquals('pages/about/index.md', $path);
    }

    public function testBuildsSimpleLocalizedPagePathWithUnpublishedDefaultLocale()
    {
        $path = $this->builder->page()->uri('/about')->locale('fr')->published(true)->defaultPublished(false)->get();

        $this->assertEquals('pages/_about/fr.index.md', $path);
    }

    public function testBuildsHomepage()
    {
        $path = $this->builder->page()->uri('/')->get();

        $this->assertEquals('pages/index.md', $path);
    }

    public function testBuildsPagePathWithParent()
    {
        $path = $this->builder->page()->uri('/about/contact')->parentPath('2.about')->get();

        $this->assertEquals('pages/2.about/contact/index.md', $path);
    }

    public function testBuildsPagePathWithForcedParentTwoLevels()
    {
        $path = $this->builder->page()->uri('/about/contact')->parentPath('1.foo/2.bar')->get();

        $this->assertEquals('pages/1.foo/2.bar/contact/index.md', $path);
    }

    public function testBuildsPagePathWithForcedParentUsingFullPath()
    {
        $path = $this->builder->page()->uri('/about/contact')->parentPath('pages/1.foo/2.bar')->get();

        $this->assertEquals('pages/1.foo/2.bar/contact/index.md', $path);
    }

    public function testBuildsUnpublishedPagePath()
    {
        $path = $this->builder->page()->uri('/about')->published(false)->get();

        $this->assertEquals('pages/_about/index.md', $path);
    }

    public function testBuildsOrderedPagePath()
    {
        $path = $this->builder->page()->uri('/about')->order(1)->get();

        $this->assertEquals('pages/1.about/index.md', $path);
    }

    public function testBuildsOrderedUnpublishedPagePath()
    {
        $path = $this->builder->page()->uri('/about')->order(1)->published(false)->get();

        $this->assertEquals('pages/_1.about/index.md', $path);
    }

    public function testBuildsSimplePagePathAsTextile()
    {
        $path = $this->builder->page()->uri('/about')->extension('textile')->get();

        $this->assertEquals('pages/about/index.textile', $path);
    }

    //-----

    /**
     * @expectedException \Exception
     */
    public function testCantBuildEntryPathWithoutCollection()
    {
        $this->builder->entry()->get();
    }

    public function testBuildsSimpleEntryPath()
    {
        $path = $this->builder->entry()->collection('blog')->slug('post')->get();

        $this->assertEquals('collections/blog/post.md', $path);
    }

    public function testBuildsUnpublishedEntryPath()
    {
        $path = $this->builder->entry()->collection('blog')->slug('post')->published(false)->get();

        $this->assertEquals('collections/blog/_post.md', $path);
    }

    public function testBuildsLocalizedEntryPath()
    {
        $path = $this->builder->entry()->collection('blog')->locale('fr')
            ->slug('post')
            ->get();

        $this->assertEquals('collections/blog/fr/post.md', $path);
    }

    public function testBuildsNumericallyOrderedEntryPath()
    {
        $path = $this->builder->entry()->collection('blog')->slug('post')->order(1)->get();

        $this->assertEquals('collections/blog/1.post.md', $path);
    }

    public function testBuildsDateOrderedEntryPath()
    {
        $path = $this->builder->entry()->collection('blog')->slug('post')->order('2015-01-01')->get();

        $this->assertEquals('collections/blog/2015-01-01.post.md', $path);
    }
}
