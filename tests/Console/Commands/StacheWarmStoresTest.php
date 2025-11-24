<?php

namespace Tests\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StacheWarmStoresTest extends TestCase
{
    #[Test]
    public function it_handles_invalid_store_names_gracefully()
    {
        $result = Artisan::call('statamic:stache:warm-stores', [
            '--stores' => 'invalid-store,another-invalid'
        ]);

        $this->assertEquals(0, $result);
    }

    #[Test]
    public function it_handles_empty_stores_parameter()
    {
        $result = Artisan::call('statamic:stache:warm-stores', [
            '--stores' => ''
        ]);

        $this->assertEquals(0, $result);
    }

    #[Test]
    public function it_handles_missing_stores_parameter()
    {
        $result = Artisan::call('statamic:stache:warm-stores');

        $this->assertEquals(0, $result);
    }

    #[Test]
    public function command_is_registered()
    {
        $commands = Artisan::all();

        $this->assertArrayHasKey('statamic:stache:warm-stores', $commands);
        $this->assertEquals(
            'Warm specific stache stores (used internally for parallel processing)',
            $commands['statamic:stache:warm-stores']->getDescription()
        );
    }
}
