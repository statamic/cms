<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class SegmentTest extends TestCase
{
    #[Test]
    public function it_gets_first_segment_by_default(): void
    {
        $this->assertEquals('foo', $this->modify('http://example.com/foo/bar/baz'));
        $this->assertEquals('foo', $this->modify('/foo/bar/baz'));
        $this->assertEquals('foo', $this->modify('foo/bar/baz'));
    }

    #[Test]
    public function it_gets_segment_by_number(): void
    {
        $this->assertEquals('bar', $this->modify('http://example.com/foo/bar/baz', 2));
        $this->assertEquals('bar', $this->modify('/foo/bar/baz', 2));
        $this->assertEquals('bar', $this->modify('foo/bar/baz', 2));
    }

    private function modify($value, $number = null)
    {
        return Modify::value($value)->segment($number)->fetch();
    }
}
