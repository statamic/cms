<?php

namespace Tests\Markdown;

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

        $this->assertEquals('bar', (new Manager($defaultParser))->foo());
    }

    /** @test */
    function it_makes_a_new_parser_instance()
    {
        $defaultParser = new Parser;

        $manager = new Manager($defaultParser);

        $config = ['foo' => 'bar'];

        $parser = $manager->makeParser($config);

        $this->assertInstanceOf(Parser::class, $parser);
        $this->assertNotSame($parser, $defaultParser);
        $this->assertEquals('bar', $parser->environment()->getConfig('foo'));
    }
}
