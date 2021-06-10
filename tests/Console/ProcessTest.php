<?php

namespace Tests\Console;

use Illuminate\Support\Facades\Log;
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

    /** @test */
    public function it_can_detect_if_process_had_errors()
    {
        $process = Process::create();

        $process->run('pwd');
        $this->assertFalse($process->hasErrorOutput());

        $process->run('not-a-command');
        $this->assertTrue($process->hasErrorOutput());
    }

    /** @test */
    public function it_resets_has_error_check_on_each_run()
    {
        $process = Process::create();

        $process->run('not-a-command');
        $this->assertTrue($process->hasErrorOutput());

        $process->run('pwd');
        $this->assertFalse($process->hasErrorOutput());

        $process->run('not-a-command');
        $this->assertTrue($process->hasErrorOutput());

        $process->runAndOperateOnOutput('pwd', function ($output) {
            return $output;
        });
        $this->assertFalse($process->hasErrorOutput());
    }

    /** @test */
    public function it_can_log_error_output()
    {
        $process = Process::create();

        Log::shouldReceive('error')->once();

        $process->run('not-a-command');

        $this->assertTrue($process->hasErrorOutput());
    }

    /** @test */
    public function it_can_run_without_logging_errors()
    {
        $process = Process::create();

        Log::shouldReceive('error')->never();

        $output = $process->withoutLoggingErrors(function ($process) {
            return $process->run('not-a-command');
        });

        $this->assertTrue($process->hasErrorOutput());
        $this->assertStringContainsString('not found', $output);
    }
}
