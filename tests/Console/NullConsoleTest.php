<?php

namespace Tests\Console;

use Statamic\Console\NullConsole;
use Tests\TestCase;

class NullConsoleTest extends TestCase
{
    /** @test */
    public function it_can_run_and_chain_methods_without_error()
    {
        $console = (new NullConsole)->info('info')->comment('comment');

        $this->assertInstanceOf(NullConsole::class, $console);
    }

    /** @test */
    public function it_can_store_and_get_console_error_output()
    {
        $console = (new NullConsole)->error('one')->error('two');

        $expected = ['one', 'two'];

        $this->assertInstanceOf(NullConsole::class, $console);
        $this->assertEquals($expected, $console->getErrors()->all());
    }
}
