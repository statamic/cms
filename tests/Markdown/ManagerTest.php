<?php

namespace Tests\Markdown;

use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;
use Statamic\Markdown\Manager;
use Statamic\Markdown\Parser;
use UnexpectedValueException;

class ManagerTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_forwards_calls_to_default_parser()
    {
        $manager = new Manager;
        $manager->extend('default', function () {
            return Mockery::mock(Parser::class)->shouldReceive('foo')->once()->andReturn('bar')->getMock();
        });

        $this->assertEquals('bar', $manager->foo());
    }

    /** @test */
    public function it_makes_a_new_parser_instance()
    {
        $manager = new Manager;
        $parser = $manager->makeParser($config = ['foo' => 'bar']);

        $this->assertInstanceOf(Parser::class, $parser);
        $this->assertNotSame($parser, $manager->parser('default'));
        $this->assertEquals('bar', $parser->environment()->getConfig('foo'));
    }

    /** @test */
    public function parser_instances_can_be_saved_and_retrieved()
    {
        $manager = new Manager;

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
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('A '.Parser::class.' instance is expected.');

        (new Manager)->extend('a', function ($parser) {
            //
        });
    }
}
