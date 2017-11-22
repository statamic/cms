<?php namespace Tests\Navigation;

use Tests\TestCase;
use Statamic\CP\Navigation\Nav;
use Statamic\CP\Navigation\NavItem;

class NavTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->nav = new Nav;
    }

    public function testCanAndRemoveItems()
    {
        $this->nav->add($this->item('content'));
        $this->assertTrue($this->nav->has('content'));

        $this->nav->remove('content');
        $this->assertFalse($this->nav->has('content'));
    }

    public function testCanCheckForChildItemsWithDotNotation()
    {
        $foo = $this->item('foo');
        $foo->add($this->item('bar'));
        $this->nav->add($foo);

        $this->assertTrue($this->nav->has('foo.bar'));
    }

    public function testCanGetChildItemsWithDotNotation()
    {
        $foo = $this->item('foo');
        $bar = $this->item('bar');
        $foo->add($bar);
        $this->nav->add($foo);

        $this->assertEquals($bar, $this->nav->get('foo.bar'));
    }

    public function testCanRemoveChildItemsWithDotNotation()
    {
        $foo = $this->item('foo');
        $bar = $this->item('bar');
        $baz = $this->item('baz');
        $bar->add($baz);
        $foo->add($bar);
        $this->nav->add($foo);
        $this->assertTrue($this->nav->has('foo.bar.baz'));

        $removed = $this->nav->remove('foo.bar.baz');

        $this->assertFalse($this->nav->has('foo.bar.baz'));
        $this->assertEquals($baz, $removed);
    }

    public function testCanTrimEmptyItems()
    {
        $one = $this->item('one');
        $one->add($this->item('one-sub'));
        $two = $this->item('two');
        $this->nav->add($one);
        $this->nav->add($two);
        $this->assertTrue($this->nav->has('one'));
        $this->assertTrue($this->nav->has('two'));

        $this->nav->trim();

        $this->assertFalse($this->nav->has('two'));
    }

    private function item($name)
    {
        $item = new NavItem;

        $item->name($name);

        return $item;
    }
}
