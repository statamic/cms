<?php

namespace Tests\Markdown;

use InvalidArgumentException;
use Mockery;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Markdown;
use Tests\TestCase;
use UnexpectedValueException;

class ManagerTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    #[Test]
    public function it_forwards_calls_to_default_parser()
    {
        $manager = app(Markdown\Manager::class);
        $manager->extend('default', function () {
            return Mockery::mock(Markdown\Parser::class)->shouldReceive('foo')->once()->andReturn('bar')->getMock();
        });

        $this->assertEquals('bar', $manager->foo());
    }

    #[Test]
    #[DefineEnvironment('configureDefaultParser')]
    public function the_default_parser_can_have_its_config_customized()
    {
        $manager = app(Markdown\Manager::class);

        $this->assertEquals(3, $manager->parser('default')->config('max_nesting_level'));
    }

    public function configureDefaultParser($app)
    {
        $app['config']->set('statamic.markdown.configs.default', [
            'max_nesting_level' => 3,
        ]);
    }

    #[Test]
    public function it_makes_a_new_parser_instance()
    {
        $manager = app(Markdown\Manager::class);

        $parser = $manager->makeParser([
            'renderer' => [
                'inner_separator' => 'foo',
            ],
            'max_nesting_level' => 3,
        ]);

        $this->assertInstanceOf(Markdown\Parser::class, $parser);
        $this->assertEquals("\n", $parser->config('renderer/block_separator'));
        $this->assertEquals('foo', $parser->config('renderer/inner_separator'));
        $this->assertEquals(3, $parser->config('max_nesting_level'));
    }

    #[Test]
    public function parser_instances_can_be_saved_and_retrieved()
    {
        $manager = new Markdown\Manager;

        config(['statamic.markdown.configs.b' => [
            'max_nesting_level' => 3,
        ]]);

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

        $parserC = null;
        $manager->extend('c', ['max_nesting_level' => 5], function ($parser) use (&$parserC) {
            return $parserC = $parser;
        });

        $this->assertSame($parserA, $manager->parser('a'));
        $this->assertNotSame($parserB, $manager->parser('a'));

        $this->assertEquals(PHP_INT_MAX, $parserA->config('max_nesting_level')); // The default is used because it wasn't customized in the config.
        $this->assertEquals(3, $parserB->config('max_nesting_level')); // Gets the customized value from the config.
        $this->assertEquals(5, $parserC->config('max_nesting_level')); // Gets the customized value from the config in the second argument.
    }

    #[Test]
    public function it_throws_an_exception_if_extending_without_returning_a_parser()
    {
        $this->expectNotToPerformAssertions();
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('A ['.Markdown\Parser::class.'] instance is expected.');

        (new Markdown\Manager)->extend('a', function ($parser) {
            //
        });
    }
}
