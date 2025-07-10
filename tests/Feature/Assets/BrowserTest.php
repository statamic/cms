<?php

namespace Tests\Feature\Assets;

use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class BrowserTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private $tempDir;

    public function setUp(): void
    {
        parent::setUp();

        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => $this->tempDir = __DIR__.'/tmp',
        ]]);
    }

    public function tearDown(): void
    {
        app('files')->deleteDirectory($this->tempDir);
        app('files')->deleteDirectory(storage_path('statamic/dimension-cache'));

        parent::tearDown();
    }

    #[Test]
    public function it_redirects_to_the_first_container_from_the_index()
    {
        $this->setTestRoles(['test' => ['access cp', 'view one assets', 'view two assets']]);
        $user = User::make()->assignRole('test')->save();
        $containerOne = AssetContainer::make('one')->save();
        $containerTwo = AssetContainer::make('two')->save();

        $this
            ->actingAs($user)
            ->get(cp_route('assets.browse.index'))
            ->assertRedirect($containerOne->showUrl());
    }

    #[Test]
    public function it_redirects_to_the_first_authorized_container_from_the_index()
    {
        $this->setTestRoles(['test' => ['access cp', 'view two assets']]);
        $user = User::make()->assignRole('test')->save();
        $containerOne = AssetContainer::make('one')->save();
        $containerTwo = AssetContainer::make('two')->save();

        $this
            ->actingAs($user)
            ->get(cp_route('assets.browse.index'))
            ->assertRedirect($containerTwo->showUrl());
    }

    #[Test]
    public function no_authorized_containers_results_in_a_403_from_the_index()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();
        $containerOne = AssetContainer::make('one')->save();
        $containerTwo = AssetContainer::make('two')->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('assets.browse.index'))
            ->assertRedirect('/original');
    }

    #[Test]
    public function no_containers_at_all_results_in_a_403_from_the_index()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('assets.browse.index'))
            ->assertRedirect('/original');
    }

    #[Test]
    public function no_containers_but_permission_to_create_redirects_to_the_index()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure asset containers']]);
        $user = User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->get(cp_route('assets.browse.index'))
            ->assertRedirect(cp_route('assets.index'));
    }

    #[Test]
    public function it_denies_access()
    {
        $container = AssetContainer::make('test')->save();

        $this
            ->from('/original')
            ->actingAs($this->userWithoutPermission())
            ->get($container->showUrl())
            ->assertRedirect('/original');
    }

    #[Test]
    public function it_shows_the_page()
    {
        $container = AssetContainer::make('test')->save();

        $this
            ->actingAs($this->userWithPermission())
            ->get($container->showUrl())
            ->assertSuccessful();
    }

    #[Test]
    public function it_lists_assets_in_the_root_folder()
    {
        $this->withoutExceptionHandling();
        $container = AssetContainer::make('test')->disk('test')->save();
        $assetOne = $container
            ->makeAsset('one.txt')
            ->upload(UploadedFile::fake()->create('one.txt'));
        $assetTwo = $container
            ->makeAsset('two.jpg')
            ->upload(UploadedFile::fake()->image('two.jpg'));
        $assetInOtherFolder = $container
            ->makeAsset('subdirectory/other.txt')
            ->upload(UploadedFile::fake()->create('other.txt'));

        $this
            ->actingAs($this->userWithPermission())
            ->getJson('/cp/assets/browse/folders/test/')
            ->assertSuccessful()
            ->assertJsonStructure($this->jsonStructure());
    }

    #[Test]
    public function it_lists_assets_in_a_subfolder()
    {
        $container = AssetContainer::make('test')->disk('test')->save();
        $assetOne = $container
            ->makeAsset('nested/subdirectory/one.txt')
            ->upload(UploadedFile::fake()->create('one.txt'));
        $assetTwo = $container
            ->makeAsset('nested/subdirectory/two.jpg')
            ->upload(UploadedFile::fake()->image('two.jpg'));
        $assetInOtherFolder = $container
            ->makeAsset('other.txt')
            ->upload(UploadedFile::fake()->create('other.txt'));

        $this
            ->actingAs($this->userWithPermission())
            ->getJson('/cp/assets/browse/folders/test/nested/subdirectory')
            ->assertSuccessful()
            ->assertJsonStructure($this->jsonStructure());
    }

    #[Test]
    public function it_denies_access_to_the_root_folder_without_permission()
    {
        AssetContainer::make('test')->disk('test')->save();

        $this
            ->actingAs($this->userWithoutPermission())
            ->getJson('/cp/assets/browse/folders/test')
            ->assertForbidden();
    }

    #[Test]
    public function it_denies_access_to_a_subfolder_without_permission()
    {
        AssetContainer::make('test')->disk('test')->save();

        $this
            ->actingAs($this->userWithoutPermission())
            ->getJson('/cp/assets/browse/folders/test/nested/subdirectory')
            ->assertForbidden();
    }

    #[Test]
    public function it_404s_when_requesting_a_folder_in_a_container_that_doesnt_exist()
    {
        $this
            ->actingAs($this->userWithPermission())
            ->getJson('/cp/assets/browse/folders/unknown')
            ->assertNotFound();
    }

    #[Test]
    public function it_searches_for_assets()
    {
        $containerOne = AssetContainer::make('one')->disk('test')->save();
        $containerTwo = AssetContainer::make('two')->disk('test')->save();

        $containerOne
            ->makeAsset('asset-one.txt')
            ->upload(UploadedFile::fake()->create('asset-one.txt'));
        $containerOne
            ->makeAsset('no-match.txt')
            ->upload(UploadedFile::fake()->create('no-match.txt'));
        $containerOne
            ->makeAsset('nested/asset-two.txt')
            ->upload(UploadedFile::fake()->create('asset-two.txt'));
        $containerOne
            ->makeAsset('nested/nope.txt')
            ->upload(UploadedFile::fake()->create('nope.txt'));
        $containerOne
            ->makeAsset('nested/subdirectory/asset-three.txt')
            ->upload(UploadedFile::fake()->create('asset-three.txt'));
        $containerTwo
            ->makeAsset('asset-four.txt')
            ->upload(UploadedFile::fake()->create('asset-four.txt'));

        $this
            ->actingAs($this->userWithPermission())
            ->getJson('/cp/assets/browse/search/one?search=asset')
            ->assertSuccessful()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.id', 'one::asset-one.txt')
            ->assertJsonPath('data.1.id', 'one::nested/asset-two.txt')
            ->assertJsonPath('data.2.id', 'one::nested/subdirectory/asset-three.txt');

        $this
            ->actingAs($this->userWithPermission())
            ->getJson('/cp/assets/browse/search/one/nested?search=asset')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 'one::nested/asset-two.txt');

        $this
            ->actingAs($this->userWithPermission())
            ->getJson('/cp/assets/browse/search/one/nested/subdirectory?search=asset')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 'one::nested/subdirectory/asset-three.txt');
    }

    #[Test]
    public function it_shows_an_assets_edit_page()
    {
        $container = AssetContainer::make('test')->disk('test')->save();
        $container
            ->makeAsset('one.txt')
            ->upload(UploadedFile::fake()->create('one.txt'));

        $this->setTestRoles(['test' => ['access cp', 'view test assets']]);
        $user = User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->getJson('/cp/assets/browse/test/one.txt/edit')
            ->assertSuccessful()
            ->assertViewIs('statamic::assets.browse');
    }

    #[Test]
    public function it_404s_when_the_asset_doesnt_exist()
    {
        $container = AssetContainer::make('test')->disk('test')->save();

        $this->setTestRoles(['test' => ['access cp', 'edit test assets']]);
        $user = User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->getJson('/cp/assets/browse/test/unknown.txt/edit')
            ->assertNotFound();
    }

    #[Test]
    public function it_denies_access_without_permission_to_view_asset()
    {
        $container = AssetContainer::make('test')->disk('test')->save();
        $container
            ->makeAsset('one.txt')
            ->upload(UploadedFile::fake()->create('one.txt'));

        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->getJson('/cp/assets/browse/test/one.txt/edit')
            ->assertForbidden();
    }

    private function userWithPermission()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test assets', 'view one assets', 'view two assets']]);

        return User::make()->assignRole('test')->save();
    }

    private function userWithoutPermission()
    {
        $this->setTestRoles(['test' => ['access cp']]);

        return User::make()->assignRole('test')->save();
    }

    private function jsonStructure()
    {
        return [
            'links' => ['folder_action', 'asset_action'],
            'data' => [
                ['id', 'size_formatted', 'last_modified_relative', 'actions'],
                ['id', 'size_formatted', 'last_modified_relative', 'actions', 'thumbnail'],
            ],
            'meta' => [
                'folder' => [
                    'title', 'path', 'parent_path', 'actions', 'folders',
                ],
            ],
        ];
    }
}
