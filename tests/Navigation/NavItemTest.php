<?php namespace Tests\Navigation;

use Tests\TestCase;
use Statamic\CP\Navigation\Nav;
use Statamic\CP\Navigation\NavItem;

class NavItemTest extends TestCase
{
    private $item;

    public function setUp()
    {
        parent::setUp();

        $this->item = new NavItem;

        $test = (new NavItem)->name('test');
        $this->item->add($test);
    }

    public function testCanGetAndSetName()
    {
        $this->assertNull($this->item->name());
        $this->item->name('foo');
        $this->assertEquals('foo', $this->item->name());
    }

    public function testCanGetAndSetTitle()
    {
        $this->assertNull($this->item->title());
        $this->item->title('foo');
        $this->assertEquals('foo', $this->item->title());
    }

    public function testNoTitleWillUseNameAsTitleCase()
    {
        $this->item->name('foo bar');
        $this->assertEquals('Foo Bar', $this->item->title());
    }

    public function testCanGetAndSetUrl()
    {
        $this->assertNull($this->item->url());
        $this->item->url('foo');
        $this->assertEquals('foo', $this->item->url());
    }

    public function testRouteSetsUrl()
    {
        $this->item->route('entries.show', 'blog');
        $this->assertEquals('http://localhost/cp/collections/entries/blog', $this->item->url());
    }

    public function testCanGetAndSetBadge()
    {
        $this->assertNull($this->item->badge());
        $this->item->badge(2);
        $this->assertEquals(2, $this->item->badge());
    }

    public function testCanGetChildren()
    {
        $this->assertInstanceOf('Illuminate\Support\Collection', $this->item->children());
    }

    public function testCanAddChildrenAndCheckForThem()
    {
        $this->assertFalse($this->item->has('foo'));

        $foo = (new NavItem)->name('foo');
        $this->item->add($foo);

        $this->assertTrue($this->item->has('foo'));
    }

    public function testCanGetChildItems()
    {
        $this->assertNull($this->item->get('foo'));

        $foo = (new NavItem)->name('foo');
        $this->item->add($foo);

        $this->assertEquals($foo, $this->item->get('foo'));
    }

    public function testCanRemoveChildren()
    {
        $this->item->remove('test');
        $this->assertFalse($this->item->has('test'));
    }
}
