<?php

namespace Tests\Console;

use Statamic\Console\Processes\Process;
use Statamic\Facades\Path;
use Tests\TestCase;

class ProcessTest extends TestCase
{
    /** @test */
    public function it_removes_ansi_codes()
    {
        $this->assertEquals(
            'Installing foo/bar',
            Process::create()->normalizeOutput("Installing \e[32mfoo/bar")
        );
    }

    /** @test */
    public function it_leaves_ansi_codes_when_colorizing()
    {
        $this->assertEquals(
            "Installing \e[32mfoo/bar",
            Process::create()->colorized()->normalizeOutput("Installing \e[32mfoo/bar")
        );
    }

    /** @test */
    public function it_can_run_process_on_custom_path()
    {
        $this->assertEquals(
            Path::resolve(resource_path()),
            Process::create(resource_path())->run('pwd')
        );

        $this->assertNotEquals(
            Path::resolve(resource_path()),
            Process::create()->run('pwd')
        );
    }
}
