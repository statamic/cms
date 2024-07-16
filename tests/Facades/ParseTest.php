<?php

namespace Tests\Facades;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Tests\TestCase;

class ParseTest extends TestCase
{
    #[Test]
    public function it_parses_front_matter()
    {
        $this->assertEquals([
            'data' => ['foo' => 'bar'],
            'content' => 'test',
        ], Parse::frontMatter("---\nfoo: bar\n---\ntest"));
    }

    #[Test]
    public function it_parses_front_matter_with_crlf()
    {
        $this->assertEquals([
            'data' => ['foo' => 'bar'],
            'content' => 'test',
        ], Parse::frontMatter("---\r\nfoo: bar\r\n---\r\ntest"));
    }

    #[Test]
    public function it_parses_front_matter_when_theres_no_fence()
    {
        $this->assertEquals([
            'data' => [],
            'content' => 'test',
        ], Parse::frontMatter('test'));
    }
}
