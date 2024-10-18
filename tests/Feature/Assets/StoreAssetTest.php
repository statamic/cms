<?php

namespace Tests\Feature\Assets;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
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

    #[Test]
    public function it_denied_access_without_permission()
    {
        $this
            ->actingAs($this->userWithoutPermission())
            ->submit()
            ->assertStatus(403);
    }

    #[Test]
    public function it_denies_access_if_uploads_are_disabled()
    {
        $this->container->allowUploads(false);

        $this
            ->actingAs($this->userWithPermission())
            ->submit()
            ->assertStatus(403);
    }

    #[Test]
    public function it_doesnt_upload_without_a_container()
    {
        $this
            ->actingAs($this->userWithPermission())
            ->submit([
                'container' => '',
            ])->assertStatus(422);
    }

    #[Test]
    public function it_doesnt_upload_without_a_folder()
    {
        $this
            ->actingAs($this->userWithPermission())
            ->submit([
                'folder' => '',
            ])->assertStatus(422);
    }

    #[Test]
    public function it_doesnt_upload_when_validation_fails()
    {
        $this->container->validationRules(['extensions:png'])->save();

        $this
            ->actingAs($this->userWithPermission())
            ->submit()
            ->assertStatus(422);
    }

    #[Test]
    public function it_doesnt_upload_when_file_exists()
    {
        Storage::disk('test')->put('path/to/test.jpg', 'contents');
        Storage::disk('test')->assertExists('path/to/test.jpg');

        $this
            ->actingAs($this->userWithPermission())
            ->submit()
            ->assertStatus(422)
            ->assertInvalid(['path' => 'A file already exists with this name.']);
    }

    #[Test]
    public function it_doesnt_upload_when_file_exists_with_different_casing()
    {
        Storage::disk('test')->put('path/to/test.jpg', 'contents');
        Storage::disk('test')->assertExists('path/to/test.jpg');

        $this
            ->actingAs($this->userWithPermission())
            ->submit([
                'file' => UploadedFile::fake()->image('tEsT.jpg'),
            ])
            ->assertStatus(422)
            ->assertInvalid(['path' => 'A file already exists with this name.']);
    }

    #[Test]
    public function it_can_upload_and_overwrite()
    {
        Storage::disk('test')->put('path/to/test.jpg', 'contents');
        Storage::disk('test')->assertExists('path/to/test.jpg');
        $this->container->makeAsset('path/to/test.jpg')->set('alt', 'A test image')->save();
        $this->assertEquals('A test image', $this->container->asset('path/to/test.jpg')->get('alt'));
        $this->assertEquals('contents', Storage::disk('test')->get('path/to/test.jpg'));
        $this->assertCount(1, Storage::disk('test')->files('path/to'));

        $this
            ->actingAs($this->userWithPermission())
            ->submit(['option' => 'overwrite'])
            ->assertOk();

        $this->assertCount(1, Storage::disk('test')->files('path/to'));
        $this->assertNotEquals('contents', Storage::disk('test')->get('path/to/test.jpg'), 'File was not overwritten.');
        $this->assertEquals('A test image', $this->container->asset('path/to/test.jpg')->get('alt'));
    }

    #[Test]
    public function it_can_upload_and_append_timestamp()
    {
        Carbon::setTestNow(Carbon::createFromTimestamp(1697379288));
        Storage::disk('test')->put('path/to/test.jpg', 'contents');
        Storage::disk('test')->assertExists('path/to/test.jpg');
        $this->assertCount(1, Storage::disk('test')->files('path/to'));

        $this
            ->actingAs($this->userWithPermission())
            ->submit(['option' => 'timestamp'])
            ->assertOk();

        $this->assertCount(2, $files = Storage::disk('test')->files('path/to'));
        $this->assertEquals(['path/to/test-1697379288.jpg', 'path/to/test.jpg'], $files);
    }

    #[Test]
    public function it_can_upload_with_different_filename()
    {
        Storage::disk('test')->put('path/to/test.jpg', 'contents');
        Storage::disk('test')->assertExists('path/to/test.jpg');
        $this->assertCount(1, Storage::disk('test')->files('path/to'));

        $this
            ->actingAs($this->userWithPermission())
            ->submit(['option' => 'rename', 'filename' => 'newname']);

        $this->assertCount(2, $files = Storage::disk('test')->files('path/to'));
        $this->assertEquals(['path/to/newname.jpg', 'path/to/test.jpg'], $files);
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
