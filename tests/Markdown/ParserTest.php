<?php

namespace Tests\Markdown;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Markdown;
use Tests\TestCase;

class ParserTest extends TestCase
{
    private $parser;

    public function setUp(): void
    {
        parent::setUp();

        $this->parser = new Markdown\Parser;
    }

    #[Test]
    public function it_parses_markdown()
    {
        $this->assertEquals("<h1>Heading One</h1>\n", $this->parser->parse('# Heading One'));
    }

    #[Test]
    public function it_adds_an_extension()
    {
        $this->assertEquals("<p>smile :)</p>\n", $this->parser->parse('smile :)'));

        $this->parser->addExtension(function () {
            return new Fixtures\SmileyExtension;
        });

        $this->assertEquals("<p>smile 😀</p>\n", $this->parser->parse('smile :)'));
    }

    #[Test]
    public function it_adds_extensions_using_an_array()
    {
        $this->assertEquals("<p>smile :) frown :(</p>\n", $this->parser->parse('smile :) frown :('));

        $this->parser->addExtensions(function () {
            return [new Fixtures\SmileyExtension, new Fixtures\FrownyExtension];
        });

        $this->assertEquals("<p>smile 😀 frown 🙁</p>\n", $this->parser->parse('smile :) frown :('));
    }

    #[Test]
    public function it_creates_a_new_instance_based_on_the_current_instance()
    {
        $this->parser->addExtension(function () {
            return new Fixtures\SmileyExtension;
        });

        $this->assertEquals("\n", $this->parser->config('renderer/block_separator'));
        $this->assertEquals("\n", $this->parser->config('renderer/inner_separator'));
        $this->assertEquals('allow', $this->parser->config('html_input'));

        $this->assertCount(1, $this->parser->extensions());

        $newParser = $this->parser->newInstance([
            'html_input' => 'strip',
            'renderer' => [
                'inner_separator' => 'foo',
            ],
        ]);

        $newParser->addExtension(function () {
            return new Fixtures\FrownyExtension;
        });

        $this->assertNotSame($this->parser, $newParser);
        $this->assertEquals("\n", $newParser->config('renderer/block_separator'));
        $this->assertEquals('foo', $newParser->config('renderer/inner_separator'));
        $this->assertEquals('strip', $newParser->config('html_input'));
        $this->assertCount(2, $newParser->extensions());
        $this->assertCount(1, $this->parser->extensions());
    }
}
