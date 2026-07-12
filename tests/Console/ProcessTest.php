<?php

namespace Tests\Console;

use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Processes\Process;
use Statamic\Facades\Path;
use Tests\TestCase;

class ProcessTest extends TestCase
{
    #[Test]
    public function it_removes_ansi_codes()
    {
        $this->assertEquals(
            'Installing foo/bar',
            Process::create()->normalizeOutput("Installing \e[32mfoo/bar")
        );
    }

    #[Test]
    public function it_leaves_ansi_codes_when_colorizing()
    {
        $this->assertEquals(
            "Installing \e[32mfoo/bar",
            Process::create()->colorized()->normalizeOutput("Installing \e[32mfoo/bar")
        );
    }

    #[Test]
    public function it_can_run_process_on_custom_path()
    {
        $this->assertEquals(
            $this->tidy(resource_path()),
            $this->tidy(trim(Process::create(resource_path())->run($this->pwdCmd())))
        );

        $this->assertNotEquals(
            $this->tidy(resource_path()),
            $this->tidy(Process::create()->run($this->pwdCmd()))
        );
    }

    #[Test]
    public function it_can_detect_if_process_had_errors()
    {
        $process = Process::create();

        $process->run($this->pwdCmd());
        $this->assertFalse($process->hasErrorOutput());

        $process->run('not-a-command');
        $this->assertTrue($process->hasErrorOutput());
    }

    #[Test]
    public function it_resets_has_error_check_on_each_run()
    {
        $process = Process::create();

        $process->run('not-a-command');
        $this->assertTrue($process->hasErrorOutput());

        $process->run($this->pwdCmd());
        $this->assertFalse($process->hasErrorOutput());

        $process->run('not-a-command');
        $this->assertTrue($process->hasErrorOutput());

        $process->runAndOperateOnOutput($this->pwdCmd(), function ($output) {
            return $output;
        });
        $this->assertFalse($process->hasErrorOutput());
    }

    #[Test]
    public function it_can_log_error_output()
    {
        $process = Process::create();

        Log::shouldReceive('error')->times(1);

        $process->run('not-a-command');

        $this->assertTrue($process->hasErrorOutput());
    }

    #[Test]
    public function it_can_run_without_logging_errors()
    {
        $process = Process::create();

        Log::shouldReceive('error')->never();

        $output = $process->withoutLoggingErrors(function ($process) {
            return $process->run('not-a-command');
        });

        $this->assertTrue($process->hasErrorOutput());
        $this->assertStringContainsString(
            static::isRunningWindows() ? 'not recognized' : 'not found',
            $output
        );
    }

    #[Test]
    public function it_can_get_cloned_process_for_running_commands_from_parent_path()
    {
        $this->assertEquals(
            $this->tidy(resource_path()),
            $this->tidy(Process::create(resource_path())->getBasePath())
        );

        $this->assertEquals(
            $this->tidy(base_path()),
            $this->tidy(Process::create(resource_path())->fromParent()->getBasePath())
        );
    }

    private function pwdCmd()
    {
        return static::isRunningWindows()
            ? 'echo %cd%'
            : 'pwd';
    }

    private function tidy($path)
    {
        return Path::tidy(trim($path));
    }
}
