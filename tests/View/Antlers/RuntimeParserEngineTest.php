<?php

namespace Tests\View\Antlers;

use Illuminate\Filesystem\Filesystem;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\View\Antlers\Parser;
use Statamic\View\Antlers\Engine;
use Tests\TestCase;

class RuntimeParserEngineTest extends TestCase
{
    private $engine;
    private $files;

    public function setUp(): void
    {
        parent::setUp();

        $this->engine = new Engine(
            $this->files = Mockery::mock(Filesystem::class),
            app(Parser::class)
        );
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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
