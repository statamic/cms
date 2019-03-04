<?php

namespace Tests;

use Statamic\Data\Content\StatusParser;

class StatusParserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Statamic\Data\Content\StatusParser
     */
    protected $parser;

    public function setUp(): void
    {
        parent::setUp();

        $this->parser = new StatusParser;
    }

    public function testGetsEntryStatusWithoutOrderPublished()
    {
        $path = 'site/content/collections/blog/post.md';

        $this->assertTrue($this->parser->entryPublished($path));
    }

    public function testGetsEntryStatusWithoutOrderUnpublished()
    {
        $path = 'site/content/collections/blog/_post.md';

        $this->assertFalse($this->parser->entryPublished($path));
    }

    public function testGetsEntryStatusPublished()
    {
        $path = 'site/content/collections/blog/1.post.md';

        $this->assertTrue($this->parser->entryPublished($path));
    }

    public function testGetsEntryStatusUnpublished()
    {
        $path = 'site/content/collections/blog/_1.post.md';

        $this->assertFalse($this->parser->entryPublished($path));
    }

    public function testGetsEntryStatusUnpublishedTwoUnderscores()
    {
        $path = 'site/content/collections/blog/__1.post.md';

        $this->assertFalse($this->parser->entryPublished($path));
    }

    public function testGetsEntryStatusUnpublishedThreeUnderscores()
    {
        $path = 'site/content/collections/blog/___1.post.md';

        $this->assertFalse($this->parser->entryPublished($path));
    }

    public function testGetsLocalizedEntryStatusPublished()
    {
        $path = 'site/content/collections/blog/fr/1.post.md';

        $this->assertTrue($this->parser->entryPublished($path));
    }

    public function testGetsLocalizedEntryStatusUnpublished()
    {
        $path = 'site/content/collections/blog/fr/_1.post.md';

        $this->assertFalse($this->parser->entryPublished($path));
    }

    public function testGetsLocalizedEntryStatusUnpublishedTwoUnderscores()
    {
        $path = 'site/content/collections/blog/fr/__1.post.md';

        $this->assertFalse($this->parser->entryPublished($path));
    }

    public function testGetsLocalizedEntryStatusUnpublishedThreeUnderscores()
    {
        $path = 'site/content/collections/blog/fr/___1.post.md';

        $this->assertFalse($this->parser->entryPublished($path));
    }

    public function testGetsPageStatusPublished()
    {
        $path = 'pages/parent/child/index.md';

        $this->assertTrue($this->parser->pagePublished($path));
    }

    public function testGetsPageStatusPublishedWithUnderscoreInSlug()
    {
        $path = 'pages/parent/child_page/index.md';

        $this->assertTrue($this->parser->pagePublished($path));
    }

    public function testGetsPageStatusUnpublishedFromChildPage()
    {
        $path = 'pages/parent/_child/index.md';

        $this->assertFalse($this->parser->pagePublished($path));
    }

    public function testGetsPageStatusUnpublishedFromChildPageWithTwoUnderscores()
    {
        $path = 'pages/parent/__child/index.md';

        $this->assertFalse($this->parser->pagePublished($path));
    }

    public function testGetsPageStatusUnpublishedFromParentPage()
    {
        $path = 'pages/_parent/child/index.md';

        $this->assertFalse($this->parser->pagePublished($path));
    }

    public function testGetsPageStatusUnpublishedFromParentPageWithTwoUnderscores()
    {
        $path = 'pages/__parent/child/index.md';

        $this->assertFalse($this->parser->pagePublished($path));
    }

    public function testGetsPageStatusUnpublishedFromParentPageWithThreeUnderscores()
    {
        $path = 'pages/___parent/child/index.md';

        $this->assertFalse($this->parser->pagePublished($path));
    }
}
