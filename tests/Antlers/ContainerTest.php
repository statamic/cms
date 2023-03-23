<?php

namespace Tests\Antlers;

use Statamic\Contracts\View\Antlers\Parser as ParserContract;
use Statamic\View\Antlers\Engine;
use Statamic\View\Antlers\Language\Runtime\RuntimeParser;
use Statamic\View\Antlers\Parser;
use Statamic\View\Cascade;
use Tests\TestCase;

class ContainerTest extends TestCase
{
    protected function useRegexParser($app)
    {
        $app->config->set('statamic.antlers.version', 'regex');
    }

    protected function useRuntimeParser($app)
    {
        $app->config->set('statamic.antlers.version', 'runtime');
    }

    /**
     * @test
     *
     * @define-env useRegexParser
     **/
    public function it_resolves_regex_parser_using_contract()
    {
        $this->assertRegexParserInstantiatedCorrectly(app(ParserContract::class));
    }

    /**
     * @test
     *
     * @define-env useRuntimeParser
     **/
    public function it_resolves_runtime_parser_using_contract()
    {
        $this->assertRuntimeParserInstantiatedCorrectly(app(ParserContract::class));
    }

    /**
     * @test
     *
     * @define-env useRegexParser
     **/
    public function it_resolves_regex_parser_using_class()
    {
        $this->assertRegexParserInstantiatedCorrectly(app(Parser::class));
    }

    /**
     * @test
     *
     * @define-env useRuntimeParser
     **/
    public function it_resolves_runtime_parser_using_class()
    {
        $this->assertRuntimeParserInstantiatedCorrectly(app(RuntimeParser::class));
    }

    private function assertRegexParserInstantiatedCorrectly($parser)
    {
        $this->assertInstanceOf(Parser::class, $parser);

        $cascade = (new \ReflectionClass($parser))->getProperty('cascade');
        $cascade->setAccessible(true);
        $this->assertEquals(app(Cascade::class), $cascade->getValue($parser));

        $callback = (new \ReflectionClass($parser))->getProperty('callback');
        $callback->setAccessible(true);
        $this->assertEquals([Engine::class, 'renderTag'], $callback->getValue($parser));
    }

    private function assertRuntimeParserInstantiatedCorrectly($parser)
    {
        $this->assertInstanceOf(RuntimeParser::class, $parser);
    }
}
