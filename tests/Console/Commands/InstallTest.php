<?php

namespace Tests\Console\Commands;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Commands\Install;
use Tests\TestCase;

class InstallTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->app[Kernel::class]->registerCommand(new class extends Install
        {
            protected function addons()
            {
                return $this;
            }

            protected function createFiles()
            {
                return $this;
            }

            protected function publish()
            {
                return $this;
            }

            protected function runCallbacks()
            {
                return $this;
            }

            protected function clearViews()
            {
                return $this;
            }

            protected function runUpdateScripts()
            {
                return $this;
            }
        });
    }

    #[Test]
    public function it_clears_the_cache()
    {
        Cache::put('foo', 'bar');

        $this->artisan('statamic:install');

        $this->assertNull(Cache::get('foo'));
    }

    #[Test]
    public function it_can_skip_clearing_the_cache()
    {
        Cache::put('foo', 'bar');

        $this->artisan('statamic:install', ['--no-cache-clear' => true]);

        $this->assertEquals('bar', Cache::get('foo'));
    }
}
