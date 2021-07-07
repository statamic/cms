<?php

namespace Tests\Console\Commands;

use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class MakeAddonTest extends TestCase
{
    protected $files;

    public function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);

        if ($this->files->exists($path = base_path('addons'))) {
            $this->files->deleteDirectory($path);
        }
    }

    /** @test */
    public function it_can_generate_an_addon()
    {
        $this->assertFileNotExists(base_path('addons/hasselhoff/knight-rider'));

        $this->makeAddon('hasselhoff/knight-rider');

        $this->assertFileExists(base_path('addons/hasselhoff/knight-rider/README.md'));
        $this->assertFileExists(base_path('addons/hasselhoff/knight-rider/composer.json'));
        $this->assertFileExists(base_path('addons/hasselhoff/knight-rider/src/ServiceProvider.php'));
    }

    /** @test */
    public function it_can_generate_with_a_fieldtype()
    {
        $this->assertFileNotExists(base_path('addons/hasselhoff/knight-rider'));

        $this->makeAddon('hasselhoff/knight-rider', ['--fieldtype' => true]);

        // Standard addon stuff
        $this->assertFileExists(base_path('addons/hasselhoff/knight-rider/README.md'));
        $this->assertFileExists(base_path('addons/hasselhoff/knight-rider/composer.json'));
        $this->assertFileExists(base_path('addons/hasselhoff/knight-rider/src/ServiceProvider.php'));

        // Fieldtype stuff
        $this->assertFileExists(base_path('addons/hasselhoff/knight-rider/package.json'));
        $this->assertFileExists(base_path('addons/hasselhoff/knight-rider/webpack.mix.js'));
        $this->assertFileExists(base_path('addons/hasselhoff/knight-rider/src/Fieldtypes/KnightRider.php'));
    }

    private function makeAddon($addon, $options = [])
    {
        $this->artisan('statamic:make:addon', array_merge([
            'package' => $addon,
            '--no-interaction' => true,
        ], $options));
    }
}
