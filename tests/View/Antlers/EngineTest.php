<?php

namespace Tests\View\Antlers;

use Illuminate\Filesystem\Filesystem;
use Mockery;
use Statamic\View\Antlers\Engine;
use Statamic\View\Antlers\Parser;
use Tests\TestCase;

class EngineTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->engine = new Engine(
            $this->files = Mockery::mock(Filesystem::class),
            app(Parser::class)
        );
    }

    /** @test */
    public function parses_a_basic_template()
    {
        $this->files
            ->shouldReceive('get')
            ->with('/path/to/foo.antlers.html')
            ->andReturn('Hello {{ foo }}');

        $this->assertEquals(
            'Hello World',
            $this->engine->get('/path/to/foo.antlers.html', ['foo' => 'World'])
        );
    }

    /** @test */
    public function parses_a_template_with_noparse_tags()
    {
        $this->files
            ->shouldReceive('get')
            ->with('/path/to/foo.antlers.html')
            ->andReturn('Hello {{ foo }} {{ noparse }}{{ bar }}{{ /noparse }} {{ bar }}');

        $this->assertEquals(
            'Hello World {{ bar }} Bar',
            $this->engine->get('/path/to/foo.antlers.html', ['foo' => 'World', 'bar' => 'Bar'])
        );
    }

    /** @test */
    public function it_can_prevent_injecting_noparse_extractions()
    {
        $this->files
            ->shouldReceive('get')
            ->with('/path/to/foo.antlers.html')
            ->andReturn('Hello {{ foo }} {{ noparse }}{{ bar }}{{ /noparse }} {{ bar }}');

        $this->assertEquals(
            'Hello World noparse_8e726b27d1e6ef37447e1aa0aaa30932 Bar',
            $this->engine
                ->withoutExtractions()
                ->get('/path/to/foo.antlers.html', ['foo' => 'World', 'bar' => 'Bar'])
        );

        // Proof that the prevention of injecting extractions only happens once.
        $this->assertEquals(
            'Hello World {{ bar }} Bar',
            $this->engine->get('/path/to/foo.antlers.html', ['foo' => 'World', 'bar' => 'Bar'])
        );
    }

    /** @test */
    public function php_is_not_executed_if_the_filename_is_html()
    {
        $this->files
            ->shouldReceive('get')
            ->with('/path/to/foo.antlers.html')
            ->andReturn('Hello <?php echo "World"; ?>');

        $this->assertEquals(
            'Hello &lt;?php echo "World"; ?>',
            $this->engine->get('/path/to/foo.antlers.html', ['foo' => 'World'])
        );
    }

    /** @test */
    public function php_is_executed_if_the_filename_is_php()
    {
        $this->files
            ->shouldReceive('get')
            ->with('/path/to/foo.antlers.php')
            ->andReturn('Hello <?php echo "World"; ?>');

        $this->assertEquals(
            'Hello World',
            $this->engine->get('/path/to/foo.antlers.php', ['foo' => 'World'])
        );
    }
}
