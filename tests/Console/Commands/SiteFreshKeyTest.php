<?php

namespace Tests\Console\Commands;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Licensing\SiteKey;
use Tests\TestCase;

class SiteFreshKeyTest extends TestCase
{
    private Filesystem $files;

    private string $envPath;

    private string $examplePath;

    public function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);
        $this->envPath = base_path('.env');
        $this->examplePath = base_path('.env.example');

        $this->files->put($this->envPath, "STATAMIC_SITE_KEY=site_abcdefghijklmnopqrstuvwxyz\n");
        $this->files->put($this->examplePath, "STATAMIC_SITE_KEY=site_abcdefghijklmnopqrstuvwxyz\n");
    }

    public function tearDown(): void
    {
        $this->files->delete($this->envPath);
        $this->files->delete($this->examplePath);

        parent::tearDown();
    }

    #[Test]
    public function it_regenerates_the_site_key_in_both_env_files()
    {
        $this->artisan('statamic:site:fresh-key')
            ->assertSuccessful();

        preg_match('/^STATAMIC_SITE_KEY=(.+)$/m', $this->files->get($this->envPath), $matches);

        $this->assertTrue((new SiteKey)->isValid($matches[1] ?? null));
        $this->assertNotEquals('site_abcdefghijklmnopqrstuvwxyz', $matches[1]);
        $this->assertStringContainsString('STATAMIC_SITE_KEY='.$matches[1], $this->files->get($this->examplePath));
        $this->assertEquals($matches[1], config('statamic.system.site_key'));
    }
}
