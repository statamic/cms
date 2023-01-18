<?php

namespace Tests\Markdown;

use InvalidArgumentException;
use Mockery;
use Statamic\Markdown;
use Tests\TestCase;
use UnexpectedValueException;

class ManagerTest extends TestCase
{
    private $parserClass;

    public function setUp(): void
    {
        parent::setUp();

        $this->parserClass = $this->isLegacyCommonmark()
            ? Markdown\LegacyParser::class
            : Markdown\Parser::class;
    }

    public function isLegacyCommonmark()
    {
        return class_exists('League\CommonMark\Inline\Element\Text');
    }

    public function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    /** @test */
    public function it_forwards_calls_to_default_parser()
    {
        $manager = app(Markdown\Manager::class);
        $manager->extend('default', function () {
            return Mockery::mock($this->parserClass)->shouldReceive('foo')->once()->andReturn('bar')->getMock();
        });

        $this->assertEquals('bar', $manager->foo());
    }

    /** @test */
    public function it_makes_a_new_parser_instance()
    {
        $manager = app(Markdown\Manager::class);

        $parser = $manager->makeParser([
            'renderer' => [
                'inner_separator' => 'foo',
            ],
            'max_nesting_level' => 3,
        ]);

        $this->assertInstanceOf($this->parserClass, $parser);
        $this->assertEquals("\n", $parser->config('renderer/block_separator'));
        $this->assertEquals('foo', $parser->config('renderer/inner_separator'));
        $this->assertEquals(3, $parser->config('max_nesting_level'));
    }

    /** @test */
    public function parser_instances_can_be_saved_and_retrieved()
    {
        $manager = new Markdown\Manager;

        try {
            $parser = $manager->parser('a');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('Markdown parser [a] is not defined.', $e->getMessage());
        }

        $parserA = null;
        $manager->extend('a', function ($parser) use (&$parserA) {
            return $parserA = $parser;
        });

        $parserB = null;
        $manager->extend('b', function ($parser) use (&$parserB) {
            return $parserB = $parser;
        });

        $this->assertSame($parserA, $manager->parser('a'));
        $this->assertNotSame($parserB, $manager->parser('a'));
    }

    /** @test */
    public function it_throws_an_exception_if_extending_without_returning_a_parser()
    {
        $this->expectNotToPerformAssertions();
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('A '.$this->parserClass.' instance is expected.');

        (new Markdown\Manager)->extend('a', function ($parser) {
            //
        });
    }
}
