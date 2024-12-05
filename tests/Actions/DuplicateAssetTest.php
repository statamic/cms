<?php

namespace Tests\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Actions\DuplicateAsset;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\Asset;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DuplicateAssetTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private $container;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('test');

        $this->container = tap(
            (new AssetContainer)->handle('test_container')->disk('test')
        )->save();
    }

    private function createAsset($filename, $alt)
    {
        $file = UploadedFile::fake()->image($filename, 30, 60);
        Storage::disk('test')->putFileAs('path/to', $file, $filename);
        Storage::disk('test')->put('path/to/.meta/'.$filename.'.yaml', "data:\n  alt: '".$alt."'");
    }

    private function assertAssetExistsAndHasData($file, $data)
    {
        Storage::disk('test')->assertExists('path/to/'.$file);
        $this->assertNotNull($asset = $this->container->asset('path/to/'.$file));
        $this->assertEquals($data, $asset->data()->all());
    }

    private function assertAssetDoesNotExist($file)
    {
        Storage::disk('test')->assertMissing($file);
        $this->assertNull($this->container->asset($file));
    }

    #[Test]
    public function it_duplicates_an_asset()
    {
        $this->createAsset('alfa.jpg', 'The alfa alt text');
        $this->createAsset('bravo.jpg', 'The bravo alt text');
        $this->createAsset('charlie.jpg', 'The charlie alt text');

        $this->assertAssetExistsAndHasData('alfa.jpg', ['alt' => 'The alfa alt text']);
        $this->assertAssetExistsAndHasData('bravo.jpg', ['alt' => 'The bravo alt text']);
        $this->assertAssetDoesNotExist('alfa-1.jpg');
        $this->assertAssetDoesNotExist('bravo-1.jpg');
        $this->assertAssetDoesNotExist('charlie-1.jpg');

        (new DuplicateAsset)->run(collect([
            Asset::find('test_container::path/to/alfa.jpg'),
            Asset::find('test_container::path/to/charlie.jpg'),
        ]), collect());

        $this->assertAssetExistsAndHasData('alfa.jpg', ['alt' => 'The alfa alt text']);
        $this->assertAssetExistsAndHasData('bravo.jpg', ['alt' => 'The bravo alt text']);
        $this->assertAssetExistsAndHasData('alfa-1.jpg', ['alt' => 'The alfa alt text', 'duplicated_from' => 'test_container::path/to/alfa.jpg']);
        $this->assertAssetExistsAndHasData('charlie-1.jpg', ['alt' => 'The charlie alt text', 'duplicated_from' => 'test_container::path/to/charlie.jpg']);
        $this->assertAssetDoesNotExist('bravo-1.jpg');

        $this->assertSame(
            Storage::disk('test')->get('path/to/alfa.jpg'),
            Storage::disk('test')->get('path/to/alfa-1.jpg')
        );

        $this->assertSame(
            Storage::disk('test')->get('path/to/charlie.jpg'),
            Storage::disk('test')->get('path/to/charlie-1.jpg')
        );
    }

    #[Test]
    public function it_increments_the_number_if_duplicate_already_exists()
    {
        $this->createAsset('alfa.jpg', 'The alfa alt text');
        $this->createAsset('alfa-1.jpg', 'Different alt text');

        (new DuplicateAsset)->run(collect([
            Asset::find('test_container::path/to/alfa.jpg'),
        ]), collect());

        $this->assertAssetExistsAndHasData('alfa.jpg', ['alt' => 'The alfa alt text']);
        $this->assertAssetExistsAndHasData('alfa-1.jpg', ['alt' => 'Different alt text']);
        $this->assertAssetExistsAndHasData('alfa-2.jpg', ['alt' => 'The alfa alt text', 'duplicated_from' => 'test_container::path/to/alfa.jpg']);
    }

    #[Test]
    public function user_with_create_permission_is_authorized()
    {
        $this->setTestRoles([
            'access' => ['upload test_container assets'],
            'noaccess' => [],
        ]);

        $userWithPermission = tap(User::make()->assignRole('access'))->save();
        $userWithoutPermission = tap(User::make()->assignRole('noaccess'))->save();
        $this->createAsset('alfa.jpg', 'The alfa alt text');
        $this->createAsset('bravo.jpg', 'The bravo alt text');

        $items = collect([
            Asset::find('test_container::path/to/alfa.jpg'),
            Asset::find('test_container::path/to/bravo.jpg'),
        ]);

        $this->assertTrue((new DuplicateAsset)->authorize($userWithPermission, $items->first()));
        $this->assertTrue((new DuplicateAsset)->authorizeBulk($userWithPermission, $items));
        $this->assertFalse((new DuplicateAsset)->authorize($userWithoutPermission, $items->first()));
        $this->assertFalse((new DuplicateAsset)->authorizeBulk($userWithoutPermission, $items));
    }
}
