<?php

namespace Tests\Markdown;

use Statamic\Console\Composer\Lock;
use Statamic\Markdown;
use Statamic\Support\Arr;
use Tests\TestCase;

class ParserTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if ($this->isLegacyCommonmark()) {
            $this->parser = new Markdown\LegacyParser;
            $this->smileyExtension = Fixtures\Legacy\SmileyExtension::class;
            $this->frownyExtension = Fixtures\Legacy\FrownyExtension::class;
        } else {
            $this->parser = new Markdown\Parser;
            $this->smileyExtension = Fixtures\SmileyExtension::class;
            $this->frownyExtension = Fixtures\FrownyExtension::class;
        }
    }

    public function isLegacyCommonmark()
    {
        $version = Lock::file(__DIR__.'/../../composer.lock')->getNormalizedInstalledVersion('league/commonmark');

        return version_compare($version, '2', '<');
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
            return new $this->smileyExtension;
        });

        $this->assertEquals("<p>smile 😀</p>\n", $this->parser->parse('smile :)'));
    }

    /** @test */
    public function it_adds_extensions_using_an_array()
    {
        $this->assertEquals("<p>smile :) frown :(</p>\n", $this->parser->parse('smile :) frown :('));

        $this->parser->addExtensions(function () {
            return [new $this->smileyExtension, new $this->frownyExtension];
        });

        $this->assertEquals("<p>smile 😀 frown 🙁</p>\n", $this->parser->parse('smile :) frown :('));
    }

    /** @test */
    public function it_creates_a_new_instance_based_on_the_current_instance()
    {
        $this->parser->addExtension(function () {
            return new $this->smileyExtension;
        });

        $config = $this->parser->config();

        $this->assertEquals("\n", $this->getFromConfig($config, 'renderer/block_separator'));
        $this->assertEquals("\n", $this->getFromConfig($config, 'renderer/inner_separator'));
        $this->assertEquals('allow', $this->getFromConfig($config, 'html_input'));

        $this->assertCount(1, $this->parser->extensions());

        $newParser = $this->parser->newInstance([
            'html_input' => 'strip',
            'renderer' => [
                'inner_separator' => 'foo',
            ],
        ]);

        $newParser->addExtension(function () {
            return new $this->frownyExtension;
        });

        $this->assertNotSame($this->parser, $newParser);
        $newConfig = $newParser->config();
        $this->assertEquals("\n", $this->getFromConfig($newConfig, 'renderer/block_separator'));
        $this->assertEquals('foo', $this->getFromConfig($newConfig, 'renderer/inner_separator'));
        $this->assertEquals('strip', $this->getFromConfig($newConfig, 'html_input'));
        $this->assertCount(2, $newParser->extensions());
        $this->assertCount(1, $this->parser->extensions());
    }

    protected function getFromConfig($config, $key)
    {
        if ($this->isLegacyCommonmark()) {
            return Arr::dot($config)[str_replace('/', '.', $key)];
        }

        return $config->get($key);
    }
}
