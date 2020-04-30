<?php

namespace Tests\Markdown;

use League\CommonMark\ConfigurableEnvironmentInterface;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Parser\InlineParserInterface;
use League\CommonMark\InlineParserContext;
use PHPUnit\Framework\TestCase;
use Statamic\Markdown\Parser;

class ParserTest extends TestCase
{
    public function setUp(): void
    {
        $this->parser = new Parser(['foo' => 'bar']);
    }

    /** @test */
    public function it_parses_markdown()
    {
        $this->assertEquals("<h1>Heading One</h1>\n", $this->parser->parse('# Heading One'));
    }

    /** @test */
    public function it_adds_an_extension()
    {
        $this->assertEquals("<p>smile :)</p>\n", $this->parser->parse('smile :)'));

        $this->parser->addExtension(function () {
            return new SmileyExtension;
        });

        $this->assertEquals("<p>smile 😀</p>\n", $this->parser->parse('smile :)'));
    }

    /** @test */
    public function it_adds_extensions_using_an_array()
    {
        $this->assertEquals("<p>smile :) frown :(</p>\n", $this->parser->parse('smile :) frown :('));

        $this->parser->addExtensions(function () {
            return [new SmileyExtension, new FrownyExtension];
        });

        $this->assertEquals("<p>smile 😀 frown 🙁</p>\n", $this->parser->parse('smile :) frown :('));
    }

    /** @test */
    public function it_creates_a_new_instance_based_on_the_current_instance()
    {
        $this->parser->addExtension(function () {
            return new SmileyExtension;
        });

        $config = $this->parser->config();
        $this->assertEquals('bar', $config['foo']);
        $this->assertArrayNotHasKey('hello', $config);
        $this->assertCount(1, $this->parser->extensions());

        $newParser = $this->parser->newInstance([
            'foo' => 'baz',
            'hello' => 'world',
        ]);

        $newParser->addExtension(function () {
            return new FrownyExtension;
        });

        $this->assertNotSame($this->parser, $newParser);
        $newConfig = $newParser->config();
        $this->assertEquals('baz', $newConfig['foo']);
        $this->assertEquals('world', $newConfig['hello']);
        $this->assertArrayNotHasKey('hello', $this->parser->config());
        $this->assertCount(2, $newParser->extensions());
        $this->assertCount(1, $this->parser->extensions());
    }
}

class SmileyExtension implements ExtensionInterface
{
    public function register(ConfigurableEnvironmentInterface $environment)
    {
        $environment->addInlineParser(new SmileyParser);
    }
}

class FrownyExtension implements ExtensionInterface
{
    public function register(ConfigurableEnvironmentInterface $environment)
    {
        $environment->addInlineParser(new FrownyParser);
    }
}

/**
 * Inspired by https://commonmark.thephpleague.com/1.0/customization/inline-parsing/.
 */
class SmileyParser implements InlineParserInterface
{
    protected $emoji = '😀';
    protected $char = ')';

    public function getCharacters(): array
    {
        return [':'];
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();

        $nextChar = $cursor->peek();
        if ($nextChar !== $this->char) {
            return false;
        }

        $cursor->advanceBy(2);

        if ($nextChar === $this->char) {
            $inlineContext->getContainer()->appendChild(new Text($this->emoji));
        }

        return true;
    }
}

class FrownyParser extends SmileyParser
{
    protected $emoji = '🙁';
    protected $char = '(';
}
