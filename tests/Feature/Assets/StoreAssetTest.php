<?php

namespace Tests\Feature\Assets;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Statamic\Assets\AssetContainer;
use Statamic\Facades;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StoreAssetTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private $container;

    public function setUp(): void
    {
        parent::setUp();

        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__.'/tmp',
        ]]);

        $this->container = (new AssetContainer)
            ->handle('test_container')
            ->disk('test')
            ->save();

        Storage::fake('test');
    }

    /** @test */
    public function it_uploads_an_asset()
    {
        Storage::disk('test')->assertMissing('path/to/test.jpg');

        $this
            ->actingAs($this->userWithPermission())
            ->submit()
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => 'test_container::path/to/test.jpg',
                    'path' => 'path/to/test.jpg',
                ],
            ]);

        Storage::disk('test')->assertExists('path/to/test.jpg');
    }

    /** @test */
    public function it_denied_access_without_permission()
    {
        $this
            ->actingAs($this->userWithoutPermission())
            ->submit()
            ->assertStatus(403);
    }

    /** @test */
    public function it_denies_access_if_uploads_are_disabled()
    {
        $this->container->allowUploads(false);

        $this
            ->actingAs($this->userWithPermission())
            ->submit()
            ->assertStatus(403);
    }

    /** @test */
    public function it_doesnt_upload_without_a_container()
    {
        $this
            ->actingAs($this->userWithPermission())
            ->submit([
                'container' => '',
            ])->assertStatus(422);
    }

    /** @test */
    public function it_doesnt_upload_without_a_folder()
    {
        $this
            ->actingAs($this->userWithPermission())
            ->submit([
                'folder' => '',
            ])->assertStatus(422);
    }

    private function submit($overrides = [])
    {
        return $this->postJson(cp_route('assets.store'), $this->validPayload($overrides));
    }

    private function validPayload($overrides)
    {
        return array_merge([
            'container' => 'test_container',
            'folder' => 'path/to',
            'file' => UploadedFile::fake()->image('test.jpg'),
        ], $overrides);
    }

    private function userWithPermission()
    {
        $this->setTestRoles(['test' => ['access cp', 'upload test_container assets']]);

        return tap(Facades\User::make()->assignRole('test'))->save();
    }

    private function userWithoutPermission()
    {
        $this->setTestRoles(['test' => ['access cp']]);

        return tap(Facades\User::make()->assignRole('test'))->save();
    }
}
