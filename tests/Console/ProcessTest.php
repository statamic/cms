<?php

namespace Tests\Console;

use Statamic\Console\Processes\Process;
use Tests\TestCase;

class ProcessTest extends TestCase
{
    /** @test */
    public function it_removes_ansi_codes()
    {
        $this->assertEquals(
            'Installing foo/bar',
            (new Process)->normalizeOutput("Installing \e[32mfoo/bar")
        );
    }

    /** @test */
    public function it_leaves_ansi_codes_when_colorizing()
    {
        $this->assertEquals(
            "Installing \e[32mfoo/bar",
            (new Process)->colorized()->normalizeOutput("Installing \e[32mfoo/bar")
        );
    }
}
