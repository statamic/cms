<?php

namespace Tests\CP\Licensing;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Statamic\Licensing\Outpost;
use Statamic\Licensing\SiteKey;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FreshSiteKeyTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private Filesystem $files;

    private string $envPath;

    private string $examplePath;

    private string $oldKey = 'site_abcdefghijklmnopqrstuvwxyz';

    public function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);
        $this->envPath = base_path('.env');
        $this->examplePath = base_path('.env.example');

        $this->files->put($this->envPath, "APP_NAME=Statamic\nSTATAMIC_SITE_KEY={$this->oldKey}\n");
        $this->files->put($this->examplePath, "APP_NAME=Statamic\nSTATAMIC_SITE_KEY={$this->oldKey}\n");

        config([
            'statamic.system.site_key' => $this->oldKey,
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
    public function it_generates_a_fresh_key_for_an_unlinked_site()
    {
        $this->fakeOutpost(['site' => ['claimed' => false]]);

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('utilities.licensing.fresh'))
            ->assertRedirect(cp_route('utilities.licensing'))
            ->assertSessionHas('success');

        preg_match('/^STATAMIC_SITE_KEY=(.+)$/m', $this->files->get($this->envPath), $matches);

        $this->assertTrue((new SiteKey)->isValid($matches[1] ?? null));
        $this->assertNotEquals($this->oldKey, $matches[1]);
        $this->assertStringContainsString('STATAMIC_SITE_KEY='.$matches[1], $this->files->get($this->examplePath));
        $this->assertEquals($matches[1], config('statamic.system.site_key'));
    }

    #[Test]
    public function it_refuses_once_the_site_is_linked()
    {
        $this->fakeOutpost(['site' => ['claimed' => true]]);

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('utilities.licensing.fresh'))
            ->assertRedirect(cp_route('utilities.licensing'))
            ->assertSessionHas('error');

        $this->assertStringContainsString('STATAMIC_SITE_KEY='.$this->oldKey, $this->files->get($this->envPath));
    }

    #[Test]
    public function it_refuses_when_a_legacy_license_key_is_in_use()
    {
        config(['statamic.system.license_key' => 'aRadLicenseKey42']);
        $this->fakeOutpost(['site' => ['claimed' => false]]);

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('utilities.licensing.fresh'))
            ->assertRedirect(cp_route('utilities.licensing'))
            ->assertSessionHas('error');

        $this->assertStringContainsString('STATAMIC_SITE_KEY='.$this->oldKey, $this->files->get($this->envPath));
    }

    #[Test]
    public function it_denies_access_without_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->post(cp_route('utilities.licensing.fresh'))
            ->assertRedirect('/cp');

        $this->assertStringContainsString('STATAMIC_SITE_KEY='.$this->oldKey, $this->files->get($this->envPath));
    }

    private function fakeOutpost(array $response): void
    {
        $outpost = $this->mock(Outpost::class);
        $outpost->shouldReceive('response')->andReturn(array_merge([
            'public' => true,
            'statamic' => ['valid' => false, 'reason' => 'unlicensed'],
            'packages' => [],
        ], $response));
        $outpost->shouldReceive('usingLicenseKeyFile')->andReturn(false);
        $outpost->shouldReceive('clearCachedResponse');
    }
}
