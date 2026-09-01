<?php

namespace Tests\Console\Commands;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Commands\Install;
use Statamic\Licensing\SiteKey;
use Tests\TestCase;

class InstallMintsSiteKeyTest extends TestCase
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

        $this->files->put($this->envPath, "APP_NAME=Statamic\n");
        $this->files->put($this->examplePath, "APP_NAME=Statamic\nSTATAMIC_SITE_KEY=\n");
    }

    public function tearDown(): void
    {
        $this->files->delete($this->envPath);
        $this->files->delete($this->examplePath);

        parent::tearDown();
    }

    #[Test]
    public function it_mints_a_site_key_into_env_files()
    {
        $command = $this->app->make(Install::class);
        $command->setLaravel($this->app);
        $command->mintSiteKey();

        $env = $this->files->get($this->envPath);
        preg_match('/^STATAMIC_SITE_KEY=(.+)$/m', $env, $matches);

        $this->assertTrue((new SiteKey)->isValid($matches[1] ?? null));
        $this->assertStringContainsString('STATAMIC_SITE_KEY='.$matches[1], $this->files->get($this->examplePath));
        $this->assertEquals($matches[1], config('statamic.system.site_key'));
    }

    #[Test]
    public function it_does_not_overwrite_an_existing_site_key()
    {
        $this->files->put($this->envPath, "STATAMIC_SITE_KEY=site_abcdefghijklmnopqrstuvwxyz\n");

        $command = $this->app->make(Install::class);
        $command->setLaravel($this->app);
        $command->mintSiteKey();

        $this->assertStringContainsString('STATAMIC_SITE_KEY=site_abcdefghijklmnopqrstuvwxyz', $this->files->get($this->envPath));
        $this->assertEquals('site_abcdefghijklmnopqrstuvwxyz', config('statamic.system.site_key'));
    }

    #[Test]
    public function it_does_not_mint_in_ci()
    {
        $_SERVER['STATAMIC_TEST_CI'] = 'true';

        try {
            $command = $this->app->make(Install::class);
            $command->setLaravel($this->app);
            $command->mintSiteKey();

            $this->assertStringNotContainsString('STATAMIC_SITE_KEY=', $this->files->get($this->envPath));
            $this->assertMatchesRegularExpression('/^STATAMIC_SITE_KEY=\s*$/m', $this->files->get($this->examplePath));
        } finally {
            unset($_SERVER['STATAMIC_TEST_CI']);
        }
    }
}
