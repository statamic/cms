<?php

namespace Tests\Feature\Fields;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Collection;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class MetaControllerTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private function fieldMeta($user, array $config, $value)
    {
        return $this
            ->actingAs($user)
            ->postJson('/cp/fields/field-meta', [
                'config' => base64_encode(json_encode($config)),
                'value' => $value,
            ]);
    }

    #[Test]
    public function it_returns_a_placeholder_for_an_unviewable_relationship_item()
    {
        Collection::make('pages')->title('Pages')->save();
        Collection::make('secret')->title('Secret')->save();

        $this->setTestRoles(['test' => ['access cp', 'view pages entries']]);
        $user = User::make()->assignRole('test')->save();

        $response = $this->fieldMeta($user, [
            'handle' => 'related',
            'type' => 'collections',
        ], ['pages', 'secret'])->assertOk();

        $data = collect($response->json('meta.data'))->keyBy('id');

        $this->assertEquals('Pages', $data['pages']['title']);
        $this->assertTrue($data['secret']['invalid']);
        $this->assertEquals('secret', $data['secret']['title']);
    }

    #[Test]
    public function the_meta_placeholder_does_not_reveal_whether_an_item_exists()
    {
        Collection::make('secret')->title('Secret')->save();

        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $response = $this->fieldMeta($user, [
            'handle' => 'related',
            'type' => 'collections',
        ], ['secret', 'does-not-exist'])->assertOk();

        $data = collect($response->json('meta.data'))->keyBy('id');

        $this->assertEquals(
            ['id' => 'secret', 'title' => 'secret', 'invalid' => true],
            $data['secret']
        );
        $this->assertEquals(
            ['id' => 'does-not-exist', 'title' => 'does-not-exist', 'invalid' => true],
            $data['does-not-exist']
        );
    }

    #[Test]
    public function it_gates_policy_less_types_through_the_preload_path()
    {
        Role::make('editor')->title('Editor')->save();

        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $response = $this->fieldMeta($user, [
            'handle' => 'roles',
            'type' => 'user_roles',
        ], ['editor'])->assertOk();

        $this->assertTrue($response->json('meta.data.0.invalid'));
        $this->assertEquals('editor', $response->json('meta.data.0.title'));
    }

    #[Test]
    public function it_returns_full_data_for_an_authorized_user()
    {
        Collection::make('pages')->title('Pages')->save();

        $this->setTestRoles(['test' => ['access cp', 'view pages entries']]);
        $user = User::make()->assignRole('test')->save();

        $response = $this->fieldMeta($user, [
            'handle' => 'related',
            'type' => 'collections',
        ], ['pages'])->assertOk();

        $this->assertEquals('Pages', $response->json('meta.data.0.title'));
        $this->assertNull($response->json('meta.data.0.invalid'));
    }

    #[Test]
    public function a_super_admin_gets_full_data_for_policy_less_types_through_preload()
    {
        Role::make('editor')->title('Editor')->save();

        $response = $this->fieldMeta(User::make()->makeSuper()->save(), [
            'handle' => 'roles',
            'type' => 'user_roles',
        ], ['editor'])->assertOk();

        $this->assertEquals('Editor', $response->json('meta.data.0.title'));
        $this->assertNull($response->json('meta.data.0.invalid'));
    }

    #[Test]
    public function it_gates_assets_through_the_preload_path()
    {
        Storage::fake('private', ['url' => '/assets']);
        Storage::disk('private')->put('two.txt', '');
        AssetContainer::make('private')->disk('private')->save();

        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $response = $this->fieldMeta($user, [
            'handle' => 'pic',
            'type' => 'assets',
            'container' => 'private',
        ], ['private::two.txt'])->assertOk();

        $data = collect($response->json('meta.data'))->keyBy('id');

        $this->assertEquals(
            ['id' => 'private::two.txt', 'url' => 'private::two.txt', 'invalid' => true],
            $data['private::two.txt']
        );
    }
}
