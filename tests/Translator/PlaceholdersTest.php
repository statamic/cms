<?php

namespace Tests\Translator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Statamic\Translator\Placeholders;

class PlaceholdersTest extends TestCase
{
    #[Test]
    public function it_adds_placeholders()
    {
        $this->assertEquals('foo <span class="notranslate">:bar</span>', $this->wrap('foo :bar'));
        $this->assertEquals('foo <span class="notranslate">:bar</span> baz', $this->wrap('foo :bar baz'));
        $this->assertEquals('foo bar <span class="notranslate">:baz</span>', $this->wrap('foo bar :baz'));
        $this->assertEquals('foo <span class="notranslate">:foo</span>', $this->wrap('foo :foo'));
        $this->assertEquals('<span class="notranslate">:foo</span> foo', $this->wrap(':foo foo'));

        $this->assertEquals('<span class="notranslate">:foo</span> foo <span class="notranslate">:bar</span> bar', $this->wrap(':foo foo :bar bar'));
    }

    #[Test]
    public function it_removes_placeholders()
    {
        $this->assertEquals('foo :bar', $this->unwrap('foo <span class="notranslate">:bar</span>'));
        $this->assertEquals('foo :bar baz', $this->unwrap('foo <span class="notranslate">:bar</span> baz'));
        $this->assertEquals('foo bar :baz', $this->unwrap('foo bar <span class="notranslate">:baz</span>'));
        $this->assertEquals('foo :foo', $this->unwrap('foo <span class="notranslate">:foo</span>'));
        $this->assertEquals(':foo foo', $this->unwrap('<span class="notranslate">:foo</span> foo'));

        $this->assertEquals(':foo foo :bar bar', $this->unwrap('<span class="notranslate">:foo</span> foo <span class="notranslate">:bar</span> bar'));
    }

    protected function wrap($text)
    {
        return (new Placeholders)->wrap($text);
    }

    protected function unwrap($text)
    {
        return (new Placeholders)->unwrap($text);
    }
}
