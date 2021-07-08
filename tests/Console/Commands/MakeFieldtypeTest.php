<?php

namespace Tests\Console\Commands;

use Tests\TestCase;

class MakeFieldtypeTest extends TestCase
{
    use Concerns\PreparesGeneratedPaths;

    public function tearDown(): void
    {
        $this->cleanupPaths();

        parent::tearDown();
    }

    /** @test */
    public function it_can_generate_a_fieldtype()
    {
        $this->assertFileNotExists(base_path('app/Fieldtypes/KnightRider.php'));
        $this->assertFileNotExists(resource_path('js/components/fieldtypes/KnightRider.vue'));

        $this->makeFieldtype('KnightRider');

        $this->assertFileExists(base_path('app/Fieldtypes/KnightRider.php'));
        $this->assertFileExists(resource_path('js/components/fieldtypes/KnightRider.vue'));

        // @TODO: Test for webpack/cp.js injection or output instructions
    }

    private function makeFieldtype($name, $options = [])
    {
        $this->artisan('statamic:make:fieldtype', array_merge([
            'name' => $name,
            '--no-interaction' => true,
        ], $options));
    }
}
