<?php

namespace Tests\Feature\Assets;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\AssetContainer;
use Statamic\CP\Assets\CropProcessor;
use Statamic\Events\AssetUploaded;
use Statamic\Facades;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CropAssetTest extends TestCase
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

        Storage::disk('test')->put('path/to/test.jpg', $this->makeImage(200, 100));
        $this->container->makeAsset('path/to/test.jpg')->set('alt', 'A test image')->save();
    }

    #[Test]
    public function it_crops_and_saves_a_copy()
    {
        Carbon::setTestNow(Carbon::createFromTimestamp(1697379288, config('app.timezone')));

        $this
            ->actingAs($this->userWithPermission())
            ->crop(['x' => 50, 'y' => 20, 'width' => 80, 'height' => 40])
            ->assertOk()
            ->assertJson(['data' => ['path' => 'path/to/test-1697379288.jpg']]);

        $this->assertCount(2, Storage::disk('test')->files('path/to'));
        $this->assertImageDimensions('path/to/test.jpg', 200, 100, 'Original should be untouched.');
        $this->assertImageDimensions('path/to/test-1697379288.jpg', 80, 40);
    }

    #[Test]
    public function it_saves_a_copy_of_a_root_folder_asset()
    {
        Storage::disk('test')->put('root.jpg', $this->makeImage(200, 100));
        $this->container->makeAsset('root.jpg')->save();

        Carbon::setTestNow(Carbon::createFromTimestamp(1697379288, config('app.timezone')));

        $this
            ->actingAs($this->userWithPermission())
            ->postJson($this->cropRoute('root.jpg'), ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100])
            ->assertOk()
            ->assertJson(['data' => ['path' => 'root-1697379288.jpg']]);

        Storage::disk('test')->assertExists('root-1697379288.jpg');
    }

    #[Test]
    public function it_crops_and_replaces_the_original()
    {
        $this
            ->actingAs($this->userWithReuploadPermission())
            ->crop(['x' => 50, 'y' => 20, 'width' => 80, 'height' => 40, 'replace' => true])
            ->assertOk()
            ->assertJson(['data' => ['path' => 'path/to/test.jpg']]);

        $this->assertCount(1, Storage::disk('test')->files('path/to'));
        $this->assertImageDimensions('path/to/test.jpg', 80, 40);
        $this->assertEquals('A test image', $this->container->asset('path/to/test.jpg')->get('alt'), 'Metadata should be preserved.');
    }

    #[Test]
    public function it_can_replace_a_jpeg_original()
    {
        Storage::disk('test')->put('path/to/photo.jpeg', $this->makeImage(200, 100));
        $this->container->makeAsset('path/to/photo.jpeg')->save();

        $this
            ->actingAs($this->userWithReuploadPermission())
            // The UI normalizes the source's "jpeg" to "jpg" and sends it as the format.
            ->postJson($this->cropRoute('path/to/photo.jpeg'), ['x' => 50, 'y' => 20, 'width' => 80, 'height' => 40, 'format' => 'jpg', 'replace' => true])
            ->assertOk()
            ->assertJson(['data' => ['path' => 'path/to/photo.jpeg']]);

        $this->assertImageDimensions('path/to/photo.jpeg', 80, 40);
    }

    #[Test]
    public function it_can_replace_an_original_with_an_uppercase_extension()
    {
        Storage::disk('test')->put('path/to/PHOTO.JPG', $this->makeImage(200, 100));
        $this->container->makeAsset('path/to/PHOTO.JPG')->save();

        $this
            ->actingAs($this->userWithReuploadPermission())
            ->postJson($this->cropRoute('path/to/PHOTO.JPG'), ['x' => 50, 'y' => 20, 'width' => 80, 'height' => 40, 'format' => 'jpg', 'replace' => true])
            ->assertOk()
            ->assertJson(['data' => ['path' => 'path/to/PHOTO.JPG']]);

        $this->assertImageDimensions('path/to/PHOTO.JPG', 80, 40);
    }

    #[Test]
    public function it_denies_saving_a_copy_without_permission()
    {
        $this
            ->actingAs($this->userWithoutPermission())
            ->crop(['x' => 0, 'y' => 0, 'width' => 80, 'height' => 40])
            ->assertStatus(403);
    }

    #[Test]
    public function it_denies_cropping_an_asset_the_user_cannot_view()
    {
        $this
            ->actingAs($this->userWithoutViewPermission())
            ->crop(['x' => 0, 'y' => 0, 'width' => 80, 'height' => 40])
            ->assertStatus(403);
    }

    #[Test]
    public function it_denies_replacing_without_reupload_permission()
    {
        $this
            ->actingAs($this->userWithPermission())
            ->crop(['x' => 0, 'y' => 0, 'width' => 80, 'height' => 40, 'replace' => true])
            ->assertStatus(403);
    }

    #[Test]
    public function it_validates_the_crop_dimensions()
    {
        $this
            ->actingAs($this->userWithPermission())
            ->crop(['x' => 0, 'y' => 0, 'width' => 0])
            ->assertStatus(422)
            ->assertInvalid(['height', 'width']);
    }

    #[Test]
    public function it_validates_the_quality()
    {
        $this
            ->actingAs($this->userWithPermission())
            ->crop(['x' => 0, 'y' => 0, 'width' => 80, 'height' => 40, 'quality' => 101])
            ->assertStatus(422)
            ->assertInvalid('quality');
    }

    #[Test]
    public function the_quality_affects_the_resulting_file_size()
    {
        $noise = $this->makeNoiseImage(300, 300);
        Storage::disk('test')->put('path/to/low.jpg', $noise);
        Storage::disk('test')->put('path/to/high.jpg', $noise);
        $this->container->makeAsset('path/to/low.jpg')->save();
        $this->container->makeAsset('path/to/high.jpg')->save();

        $user = $this->userWithReuploadPermission();

        $this->actingAs($user)
            ->postJson($this->cropRoute('path/to/low.jpg'), ['x' => 0, 'y' => 0, 'width' => 200, 'height' => 200, 'quality' => 10, 'replace' => true])
            ->assertOk();

        $this->actingAs($user)
            ->postJson($this->cropRoute('path/to/high.jpg'), ['x' => 0, 'y' => 0, 'width' => 200, 'height' => 200, 'quality' => 95, 'replace' => true])
            ->assertOk();

        $this->assertLessThan(
            strlen(Storage::disk('test')->get('path/to/high.jpg')),
            strlen(Storage::disk('test')->get('path/to/low.jpg')),
        );
    }

    #[Test]
    public function it_cannot_crop_a_non_image()
    {
        Storage::disk('test')->put('path/to/doc.txt', 'not an image');
        $this->container->makeAsset('path/to/doc.txt')->save();

        $this
            ->actingAs($this->userWithPermission())
            ->postJson($this->cropRoute('path/to/doc.txt'), ['x' => 0, 'y' => 0, 'width' => 10, 'height' => 10])
            ->assertStatus(422);
    }

    #[Test]
    public function it_converts_to_a_different_format_when_saving_a_copy()
    {
        $this
            ->actingAs($this->userWithPermission())
            ->crop(['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100, 'format' => 'webp'])
            ->assertOk()
            ->assertJson(['data' => ['path' => 'path/to/test.webp']]);

        Storage::disk('test')->assertExists('path/to/test.webp');
        $this->assertImageDimensions('path/to/test.jpg', 200, 100, 'Original should be untouched.');
    }

    #[Test]
    public function it_cannot_change_the_format_when_replacing_the_original()
    {
        $this
            ->actingAs($this->userWithReuploadPermission())
            ->crop(['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100, 'format' => 'webp', 'replace' => true])
            ->assertStatus(422);
    }

    #[Test]
    public function it_can_crop_an_avif_image()
    {
        // The crop is mocked because encoding AVIF requires GD/Imagick to be
        // built with libavif, which isn't guaranteed. This asserts the request
        // is accepted and saved as a copy rather than rejected for its format.
        $this->mock(CropProcessor::class)->shouldReceive('crop')->once()->andReturn('cropped');

        // Prevent the post-upload thumbnail generation, which would try to read
        // the mocked (non-image) output and has the same AVIF codec dependency.
        Event::fake([AssetUploaded::class]);

        Storage::disk('test')->put('path/to/photo.avif', 'source');
        $this->container->makeAsset('path/to/photo.avif')->save();

        Carbon::setTestNow(Carbon::createFromTimestamp(1697379288, config('app.timezone')));

        $this
            ->actingAs($this->userWithPermission())
            ->postJson($this->cropRoute('path/to/photo.avif'), ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100])
            ->assertOk()
            ->assertJson(['data' => ['path' => 'path/to/photo-1697379288.avif']]);

        $this->assertEquals('cropped', Storage::disk('test')->get('path/to/photo-1697379288.avif'));
    }

    #[Test]
    public function it_validates_the_format()
    {
        $this
            ->actingAs($this->userWithPermission())
            ->crop(['x' => 0, 'y' => 0, 'width' => 80, 'height' => 40, 'format' => 'tiff'])
            ->assertStatus(422)
            ->assertInvalid('format');
    }

    #[Test]
    public function it_validates_the_output_against_the_containers_allowed_file_types()
    {
        $this->container->validationRules(['extensions:jpg'])->save();

        $this
            ->actingAs($this->userWithPermission())
            ->crop(['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100, 'format' => 'webp'])
            ->assertStatus(422);
    }

    #[Test]
    public function it_flattens_transparency_onto_the_chosen_background_when_converting_to_jpeg()
    {
        Storage::disk('test')->put('path/to/black.png', $this->makeTransparentImage(200, 100));
        Storage::disk('test')->put('path/to/white.png', $this->makeTransparentImage(200, 100));
        $this->container->makeAsset('path/to/black.png')->save();
        $this->container->makeAsset('path/to/white.png')->save();

        $user = $this->userWithPermission();

        $this->actingAs($user)
            ->postJson($this->cropRoute('path/to/black.png'), ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100, 'format' => 'jpg', 'background' => 'black'])
            ->assertOk()
            ->assertJson(['data' => ['path' => 'path/to/black.jpg']]);

        $this->actingAs($user)
            ->postJson($this->cropRoute('path/to/white.png'), ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100, 'format' => 'jpg', 'background' => 'white'])
            ->assertOk();

        $this->assertLessThan(40, $this->redChannel('path/to/black.jpg'));
        $this->assertGreaterThan(215, $this->redChannel('path/to/white.jpg'));
    }

    private function crop($payload)
    {
        return $this->postJson($this->cropRoute('path/to/test.jpg'), $payload);
    }

    private function cropRoute($path)
    {
        return cp_route('assets.crop', ['encoded_asset' => base64_encode('test_container::'.$path)]);
    }

    private function makeImage($width, $height)
    {
        return (string) ImageManager::gd()->create($width, $height)->fill('ff0000')->encodeByExtension('jpg');
    }

    private function makeTransparentImage($width, $height)
    {
        return (string) ImageManager::gd()->create($width, $height)->encodeByExtension('png');
    }

    private function redChannel($path)
    {
        $hex = ImageManager::gd()->read(Storage::disk('test')->get($path))->pickColor(5, 5)->toHex();

        return hexdec(substr($hex, 0, 2));
    }

    private function makeNoiseImage($width, $height)
    {
        mt_srand(1);
        $gd = imagecreatetruecolor($width, $height);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                imagesetpixel($gd, $x, $y, imagecolorallocate($gd, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)));
            }
        }

        ob_start();
        imagejpeg($gd, null, 100);
        $data = ob_get_clean();
        imagedestroy($gd);

        return $data;
    }

    private function assertImageDimensions($path, $width, $height, $message = '')
    {
        [$actualWidth, $actualHeight] = getimagesizefromstring(Storage::disk('test')->get($path));

        $this->assertEquals([$width, $height], [$actualWidth, $actualHeight], $message);
    }

    private function userWithPermission()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test_container assets', 'upload test_container assets']]);

        return tap(Facades\User::make()->assignRole('test'))->save();
    }

    private function userWithReuploadPermission()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test_container assets', 'upload test_container assets', 'edit test_container assets']]);

        return tap(Facades\User::make()->assignRole('test'))->save();
    }

    private function userWithoutViewPermission()
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
