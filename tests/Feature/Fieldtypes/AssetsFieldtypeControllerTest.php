<?php

namespace Tests\Feature\Fieldtypes;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AssetsFieldtypeControllerTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('public', ['url' => '/assets']);
        Storage::fake('private', ['url' => '/assets']);
        Storage::disk('public')->put('one.txt', '');
        Storage::disk('private')->put('two.txt', '');

        AssetContainer::make('public')->disk('public')->save();
        AssetContainer::make('private')->disk('private')->save();
    }

    #[Test]
    public function it_returns_data_for_viewable_assets_and_placeholders_for_unviewable_ones()
    {
        $this->setTestRoles(['test' => ['access cp', 'view public assets']]);
        $user = User::make()->assignRole('test')->save();

        $response = $this
            ->actingAs($user)
            ->postJson('/cp/assets-fieldtype', [
                'assets' => ['public::one.txt', 'private::two.txt'],
            ])
            ->assertOk();

        $data = collect($response->json())->keyBy('id');

        $this->assertCount(2, $data);

        $this->assertArrayNotHasKey('invalid', $data['public::one.txt']);

        $this->assertTrue($data['private::two.txt']['invalid']);
        $this->assertEquals('private::two.txt', $data['private::two.txt']['url']);
    }

    #[Test]
    public function an_unviewable_asset_is_not_silently_dropped()
    {
        $this->setTestRoles(['test' => ['access cp', 'view public assets']]);
        $user = User::make()->assignRole('test')->save();

        $response = $this
            ->actingAs($user)
            ->postJson('/cp/assets-fieldtype', [
                'assets' => ['private::two.txt'],
            ])
            ->assertOk();

        $this->assertCount(1, $response->json());
        $this->assertTrue($response->json('0.invalid'));
    }

    #[Test]
    public function the_placeholder_does_not_reveal_whether_an_asset_exists()
    {
        $this->setTestRoles(['test' => ['access cp', 'view public assets']]);
        $user = User::make()->assignRole('test')->save();

        $response = $this
            ->actingAs($user)
            ->postJson('/cp/assets-fieldtype', [
                'assets' => ['private::two.txt', 'private::missing.txt'],
            ])
            ->assertOk();

        $data = collect($response->json())->keyBy('id');

        $this->assertEquals(
            ['id' => 'private::two.txt', 'url' => 'private::two.txt', 'invalid' => true],
            $data['private::two.txt']
        );
        $this->assertEquals(
            ['id' => 'private::missing.txt', 'url' => 'private::missing.txt', 'invalid' => true],
            $data['private::missing.txt']
        );
    }

    #[Test]
    public function a_super_admin_gets_full_asset_data()
    {
        $response = $this
            ->actingAs(User::make()->makeSuper()->save())
            ->postJson('/cp/assets-fieldtype', [
                'assets' => ['private::two.txt'],
            ])
            ->assertOk();

        $this->assertEquals('private::two.txt', $response->json('0.id'));
        $this->assertNull($response->json('0.invalid'));
    }
}
