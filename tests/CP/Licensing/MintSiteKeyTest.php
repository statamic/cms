<?php

namespace Tests\CP\Licensing;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Statamic\Licensing\SiteKey;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class MintSiteKeyTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

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

        config([
            'statamic.system.site_key' => null,
            'statamic.system.license_key' => null,
        ]);
    }

    public function tearDown(): void
    {
        $this->files->delete($this->envPath);
        $this->files->delete($this->examplePath);

        parent::tearDown();
    }

    #[Test]
    public function it_mints_a_site_key_from_the_control_panel()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->from(cp_route('utilities.licensing'))
            ->post(cp_route('utilities.licensing.mint'))
            ->assertRedirect(cp_route('utilities.licensing'))
            ->assertSessionHas('success');

        preg_match('/^STATAMIC_SITE_KEY=(.+)$/m', $this->files->get($this->envPath), $matches);

        $this->assertTrue((new SiteKey)->isValid($matches[1] ?? null));
        $this->assertStringContainsString('STATAMIC_SITE_KEY='.$matches[1], $this->files->get($this->examplePath));
    }

    #[Test]
    public function it_does_not_overwrite_an_existing_key()
    {
        config(['statamic.system.site_key' => 'site_abcdefghijklmnopqrstuvwxyz']);

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('utilities.licensing.mint'))
            ->assertRedirect(cp_route('utilities.licensing'))
            ->assertSessionHas('error');

        $this->assertStringNotContainsString('STATAMIC_SITE_KEY=site_', $this->files->get($this->envPath));
    }

    #[Test]
    public function it_denies_access_without_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->post(cp_route('utilities.licensing.mint'))
            ->assertRedirect('/cp');

        $this->assertStringNotContainsString('STATAMIC_SITE_KEY=', $this->files->get($this->envPath));
    }
}
