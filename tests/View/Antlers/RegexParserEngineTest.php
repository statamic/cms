<?php

namespace Tests\View\Antlers;

use Tests\TestCase;

class RegexParserEngineTest extends TestCase
{
    use EngineTests;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app->config->set('statamic.antlers.version', 'regex');
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
}
