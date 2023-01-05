<?php

namespace Tests\Feature\Fieldtypes;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FilesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        config(['statamic.assets.image_manipulation.presets.upload' => [
            'w' => '15',
            'h' => '20',
            'fit' => 'crop',
        ]]);

        Storage::fake('with_preset');
        Storage::fake('without_preset');

        AssetContainer::make('without_preset')->disk('without_preset')->save();
        AssetContainer::make('with_preset')->disk('with_preset')->glideSourcePreset('upload')->save();
    }

    /**
     * @test
     * @dataProvider uploadProvider
     */
    public function it_uploads_a_file($container, $file, $expectedPath, $expectedWidth, $expectedHeight)
    {
        Carbon::setTestNow(Carbon::createFromTimestamp(1671484636));

        $disk = Storage::fake('local');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->post('/cp/fieldtypes/files/upload', [
                'file' => $file,
                'container' => $container,
            ])
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $path = $expectedPath,
                ],
            ]);

        $disk->assertExists('statamic/file-uploads/'.$path);

        if ($expectedWidth) {
            [$width, $height] = getimagesize($disk->path('statamic/file-uploads/'.$path));
            $this->assertEquals($expectedWidth, $width);
            $this->assertEquals($expectedHeight, $height);
        }
    }

    public function uploadProvider()
    {
        $image = UploadedFile::fake()->image('test.jpg', 50, 75);
        $text = UploadedFile::fake()->create('test.txt');

        return [
            'no container' => [null, $image, '1671484636/test.jpg', 50, 75],
            'container with no preset' => ['without_preset', $image, '1671484636/test.jpg', 50, 75],
            'container with preset' => ['with_preset', $image, '1671484636/test.jpg', 15, 20],
            'non-image with container' => [null, $text, '1671484636/test.txt', null, null],
            'non-image with container with no preset' => ['without_preset', $text, '1671484636/test.txt', null, null],
            'non-image with container with preset' => ['with_preset', $text, '1671484636/test.txt', null, null],
        ];
    }
}
