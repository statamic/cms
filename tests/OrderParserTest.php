<?php

namespace Tests;

use Statamic\Data\Content\OrderParser;

class OrderParserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Statamic\Data\Content\OrderParser
     */
    protected $parser;

    public function setUp(): void
    {
        parent::setUp();

        $this->parser = new OrderParser;
    }

    public function testGetsPageOrder()
    {
        $path = 'site/content/pages/1.about/2.contact/index.md';

        $this->assertEquals(2, $this->parser->getPageOrder($path));
    }

    public function testGetsNoPageOrder()
    {
        $path = 'site/content/pages/1.about/contact/index.md';

        $this->assertEquals(null, $this->parser->getPageOrder($path));
    }

    public function testGetsHiddenPageOrder()
    {
        $path = 'site/content/pages/1.about/_2.contact/index.md';

        $this->assertEquals(2, $this->parser->getPageOrder($path));
    }

    public function testGetsDraftPageOrder()
    {
        $path = 'site/content/pages/1.about/__2.contact/index.md';

        $this->assertEquals(2, $this->parser->getPageOrder($path));
    }

    public function testGetsLocalizedPageOrder()
    {
        $path = 'site/content/pages/1.about/2.contact/fr.index.md';

        $this->assertEquals(2, $this->parser->getPageOrder($path));
    }

    public function testGetsNoLocalizedPageOrder()
    {
        $path = 'site/content/pages/1.about/contact/fr.index.md';

        $this->assertEquals(null, $this->parser->getPageOrder($path));
    }

    public function testGetsEntryNumericOrder()
    {
        $path = 'site/content/collections/blog/1.post.md';

        $this->assertEquals(1, $this->parser->getEntryOrder($path));
    }

    public function testGetsEntryDateOrder()
    {
        $this->assertEquals(
            '2015-01-01',
            $this->parser->getEntryOrder('site/content/collections/blog/2015-01-01.post.md')
        );

        $this->assertEquals(
            '2015-01-01',
            $this->parser->getEntryOrder('site/content/collections/blog/_2015-01-01.post.md')
        );

        $this->assertEquals(
            '2015-01-01',
            $this->parser->getEntryOrder('site/content/collections/blog/__2015-01-01.post.md')
        );
    }

    public function testGetsEntryTimeOrder()
    {
        $this->assertEquals(
            '2015-01-01-1300',
            $this->parser->getEntryOrder('site/content/collections/blog/2015-01-01-1300.post.md')
        );

        $this->assertEquals(
            '2015-01-01-1300',
            $this->parser->getEntryOrder('site/content/collections/blog/_2015-01-01-1300.post.md')
        );

        $this->assertEquals(
            '2015-01-01-1300',
            $this->parser->getEntryOrder('site/content/collections/blog/__2015-01-01-1300.post.md')
        );
    }

    public function testGetsNoEntryOrder()
    {
        $path = 'site/content/collections/blog/post.md';

        $this->assertEquals(null, $this->parser->getEntryOrder($path));
    }

    public function testEnsuresNumericEntryOrderIsInteger()
    {
        $path = 'site/content/collections/blog/1.post.md';

        $this->assertTrue(is_int($this->parser->getEntryOrder($path)));
    }

    public function testEnsuresDateEntryOrderIsString()
    {
        $path = 'site/content/collections/blog/2015-01-01.post.md';

        $this->assertTrue(is_string($this->parser->getEntryOrder($path)));
    }

    public function testEnsuresPageOrderIsInteger()
    {
        $path = 'site/content/pages/1.about/2.contact/index.md';

        $this->assertTrue(is_int($this->parser->getPageOrder($path)));
    }
}
