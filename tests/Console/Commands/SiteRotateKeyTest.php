<?php

namespace Tests\Console\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Licensing\KeyRotation;
use Statamic\Licensing\SiteKey;
use Tests\TestCase;

class SiteRotateKeyTest extends TestCase
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
        config(['statamic.system.site_key' => 'site_abcdefghijklmnopqrstuvwxyz']);
    }

    public function tearDown(): void
    {
        $this->files->delete($this->envPath);
        $this->files->delete($this->examplePath);

        parent::tearDown();
    }

    #[Test]
    public function it_rotates_the_key_and_notifies_statamic_com()
    {
        Http::fake([
            KeyRotation::URL => Http::response(['ok' => true]),
        ]);

        $this->artisan('statamic:site:rotate-key', ['--force' => true])->assertSuccessful();

        preg_match('/^STATAMIC_SITE_KEY=(.+)$/m', $this->files->get($this->envPath), $matches);

        $this->assertTrue((new SiteKey)->isValid($matches[1] ?? null));
        $this->assertNotEquals('site_abcdefghijklmnopqrstuvwxyz', $matches[1]);
        $this->assertStringContainsString('STATAMIC_SITE_KEY='.$matches[1], $this->files->get($this->examplePath));

        Http::assertSent(fn ($request) => $request->url() === KeyRotation::URL
            && $request['old_key'] === 'site_abcdefghijklmnopqrstuvwxyz'
            && $request['new_key'] === $matches[1]);
    }
}
