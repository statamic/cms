<?php

namespace Tests\Licensing;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Licensing\SiteKey;
use Tests\TestCase;

class SiteKeyTest extends TestCase
{
    private string $dir;

    public function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir().'/statamic-site-key-'.uniqid();
        File::makeDirectory($this->dir);
    }

    public function tearDown(): void
    {
        File::deleteDirectory($this->dir);

        parent::tearDown();
    }

    #[Test]
    public function it_generates_a_valid_site_key()
    {
        $key = (new SiteKey)->generate();

        $this->assertTrue((new SiteKey)->isValid($key));
        $this->assertMatchesRegularExpression('/^site_[a-zA-Z0-9]{26}$/', $key);
    }

    #[Test]
    public function it_writes_a_generated_key_to_blank_env_files()
    {
        File::put($env = $this->dir.'/.env', "APP_NAME=Statamic\n");
        File::put($example = $this->dir.'/.env.example', "APP_NAME=Statamic\nSTATAMIC_SITE_KEY=\n");

        $key = (new SiteKey)->ensure($env, $example);

        $this->assertTrue((new SiteKey)->isValid($key));
        $this->assertStringContainsString('STATAMIC_SITE_KEY='.$key, File::get($env));
        $this->assertStringContainsString('STATAMIC_SITE_KEY='.$key, File::get($example));
    }

    #[Test]
    public function it_does_not_overwrite_a_populated_env_key()
    {
        File::put($env = $this->dir.'/.env', "STATAMIC_SITE_KEY=site_abcdefghijklmnopqrstuvwxyz\n");
        File::put($example = $this->dir.'/.env.example', "APP_NAME=Statamic\n");

        $key = (new SiteKey)->ensure($env, $example);

        $this->assertEquals('site_abcdefghijklmnopqrstuvwxyz', $key);
        $this->assertEquals("STATAMIC_SITE_KEY=site_abcdefghijklmnopqrstuvwxyz\n", File::get($env));
        $this->assertStringContainsString('STATAMIC_SITE_KEY=site_abcdefghijklmnopqrstuvwxyz', File::get($example));
    }

    #[Test]
    public function it_does_not_mint_in_ci()
    {
        $_SERVER['STATAMIC_TEST_CI'] = 'true';

        try {
            File::put($env = $this->dir.'/.env', "APP_NAME=Statamic\n");
            File::put($example = $this->dir.'/.env.example', "APP_NAME=Statamic\n");

            $this->assertNull((new SiteKey)->ensure($env, $example));
            $this->assertStringNotContainsString('STATAMIC_SITE_KEY=', File::get($env));
        } finally {
            unset($_SERVER['STATAMIC_TEST_CI']);
        }
    }

    #[Test]
    public function write_overwrites_both_files()
    {
        File::put($env = $this->dir.'/.env', "STATAMIC_SITE_KEY=site_abcdefghijklmnopqrstuvwxyz\n");
        File::put($example = $this->dir.'/.env.example', "STATAMIC_SITE_KEY=site_abcdefghijklmnopqrstuvwxyz\n");

        $key = (new SiteKey)->write('site_zyxwvutsrqponmlkjihgfedcba', $env, $example);

        $this->assertEquals('site_zyxwvutsrqponmlkjihgfedcba', $key);
        $this->assertStringContainsString('STATAMIC_SITE_KEY=site_zyxwvutsrqponmlkjihgfedcba', File::get($env));
        $this->assertStringContainsString('STATAMIC_SITE_KEY=site_zyxwvutsrqponmlkjihgfedcba', File::get($example));
    }
}
