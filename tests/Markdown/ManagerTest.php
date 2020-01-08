<?php

namespace Tests\Markdown;

use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;
use Statamic\Markdown\Manager;
use Statamic\Markdown\Parser;

class ManagerTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    function it_forwards_calls_to_default_parser()
    {
        $defaultParser = Mockery::mock(Parser::class);
        $defaultParser->shouldReceive('foo')->once()->andReturn('bar');

        $manager = new Manager;
        $manager->setParser('default', $defaultParser);

        $this->assertEquals('bar', $manager->foo());
    }

    /** @test */
    function it_makes_a_new_parser_instance()
    {
        $manager = new Manager;
        $manager->setParser('default', $defaultParser = new Parser);

        $parser = $manager->makeParser($config = ['foo' => 'bar']);

        $this->assertInstanceOf(Parser::class, $parser);
        $this->assertNotSame($parser, $defaultParser);
        $this->assertEquals('bar', $parser->environment()->getConfig('foo'));
    }

    /** @test */
    function parser_instances_can_be_saved_and_retrieved()
    {
        $manager = new Manager;

        try {
            $parser = $manager->parser('a');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('Markdown parser [a] is not defined.', $e->getMessage());
        }

        $parserA = $manager->makeParser();
        $parserB = $manager->makeParser();

        $manager->setParser('a', $parserA);

        $this->assertSame($parserA, $manager->parser('a'));
        $this->assertNotSame($parserB, $manager->parser('a'));
    }
}
