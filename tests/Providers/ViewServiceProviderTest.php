<?php

namespace Tests\Providers;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Providers\ViewServiceProvider;
use Tests\TestCase;

class ViewServiceProviderTest extends TestCase
{
    #[Test]
    public function it_creates_the_nocache_view_directory()
    {
        File::deleteDirectory($directory = storage_path('statamic/tmp/nocache'));

        (new ViewServiceProvider($this->app))->boot();

        $this->assertDirectoryExists($directory);
    }
}
