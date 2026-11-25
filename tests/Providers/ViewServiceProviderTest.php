<?php

namespace Tests\Providers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Providers\ViewServiceProvider;
use Tests\TestCase;

class ViewServiceProviderTest extends TestCase
{
    #[Test]
    public function it_creates_the_nocache_view_directory()
    {
        app('files')->deleteDirectory($directory = storage_path('statamic/tmp/nocache'));

        (new ViewServiceProvider($this->app))->boot();

        $this->assertDirectoryExists($directory);
    }

    #[Test]
    public function it_lets_the_umask_decide_the_nocache_view_directory_permissions()
    {
        $this->markTestSkippedInWindows('Windows does not support Unix file permissions.');

        app('files')->deleteDirectory($directory = storage_path('statamic/tmp/nocache'));

        $originalUmask = umask(0002);

        try {
            (new ViewServiceProvider($this->app))->boot();
        } finally {
            umask($originalUmask);
        }

        $this->assertSame('0775', substr(sprintf('%o', fileperms($directory)), -4));
    }
}
