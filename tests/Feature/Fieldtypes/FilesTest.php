<?php

namespace Tests\Feature\Fieldtypes;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Assets\DimensionsRule;
use Statamic\Fieldtypes\Assets\ImageRule;
use Statamic\Fieldtypes\Assets\MaxRule;
use Statamic\Fieldtypes\Assets\MimesRule;
use Statamic\Fieldtypes\Assets\MimetypesRule;
use Statamic\Fieldtypes\Assets\MinRule;
use Statamic\Fieldtypes\Files;
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
        AssetContainer::make('with_preset')->disk('with_preset')->sourcePreset('upload')->save();
    }

    #[Test]
    #[DataProvider('uploadProvider')]
    public function it_uploads_a_file($container, $isImage, $expectedPath, $expectedWidth, $expectedHeight)
    {
        $glideDir = storage_path('statamic/glide/tmp');
        app('files')->deleteDirectory($glideDir);

        $file = $isImage
            ? UploadedFile::fake()->image('test.jpg', 50, 75)
            : UploadedFile::fake()->create('test.txt');

        Date::setTestNow(Date::createFromTimestamp(1671484636, config('app.timezone')));

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

        // When a container with a preset is used, and the file is an image, make sure it's cleaned up.
        if ($container === 'with_preset' && $isImage) {
            $this->assertDirectoryExists($glideDir);
            $this->assertEmpty(app('files')->allFiles($glideDir)); // no temp files
        }
    }

    public static function uploadProvider()
    {
        return [
            'no container' => [null, true, '1671484636/test.jpg', 50, 75],
            'container with no preset' => ['without_preset', true, '1671484636/test.jpg', 50, 75],
            'container with preset' => ['with_preset', true, '1671484636/test.jpg', 15, 20],
            'non-image with container' => [null, false, '1671484636/test.txt', null, null],
            'non-image with container with no preset' => ['without_preset', false, '1671484636/test.txt', null, null],
            'non-image with container with preset' => ['with_preset', false, '1671484636/test.txt', null, null],
        ];
    }

    #[Test]
    public function it_replaces_dimensions_rule()
    {
        $replaced = $this->fieldtype(['validate' => ['dimensions:width=180,height=180']])->fieldRules();

        $this->assertIsArray($replaced);
        $this->assertCount(1, $replaced);
        $this->assertInstanceOf(DimensionsRule::class, $replaced[0]);
    }

    #[Test]
    public function it_replaces_image_rule()
    {
        $replaced = $this->fieldtype(['validate' => ['image']])->fieldRules();

        $this->assertIsArray($replaced);
        $this->assertCount(1, $replaced);
        $this->assertInstanceOf(ImageRule::class, $replaced[0]);
    }

    #[Test]
    public function it_replaces_mimes_rule()
    {
        $replaced = $this->fieldtype(['validate' => ['mimes:jpg,png']])->fieldRules();

        $this->assertIsArray($replaced);
        $this->assertCount(1, $replaced);
        $this->assertInstanceOf(MimesRule::class, $replaced[0]);
    }

    #[Test]
    public function it_replaces_mimetypes_rule()
    {
        $replaced = $this->fieldtype(['validate' => ['mimetypes:image/jpg,image/png']])->fieldRules();

        $this->assertIsArray($replaced);
        $this->assertCount(1, $replaced);
        $this->assertInstanceOf(MimetypesRule::class, $replaced[0]);
    }

    #[Test]
    public function it_replaces_min_filesize_rule()
    {
        $replaced = $this->fieldtype(['validate' => ['min_filesize:100']])->fieldRules();

        $this->assertIsArray($replaced);
        $this->assertCount(1, $replaced);
        $this->assertInstanceOf(MinRule::class, $replaced[0]);
    }

    #[Test]
    public function it_replaces_max_filesize_rule()
    {
        $replaced = $this->fieldtype(['validate' => ['max_filesize:100']])->fieldRules();

        $this->assertIsArray($replaced);
        $this->assertCount(1, $replaced);
        $this->assertInstanceOf(MaxRule::class, $replaced[0]);
    }

    #[Test]
    public function it_doesnt_replace_non_file_related_rule()
    {
        $replaced = $this->fieldtype(['validate' => ['required']])->fieldRules();

        $this->assertIsArray($replaced);
        $this->assertCount(1, $replaced);
        $this->assertEquals('required', $replaced[0]);
    }

    #[Test]
    public function it_passes_through_closure_rules()
    {
        $closure = function ($attribute, $value, $fail) {
            // custom validation
        };

        $replaced = $this->fieldtype(['validate' => [$closure]])->fieldRules();

        $this->assertIsArray($replaced);
        $this->assertCount(1, $replaced);
        $this->assertSame($closure, $replaced[0]);
    }

    private function fieldtype($config = [])
    {
        return (new Files)->setField(new Field('test', array_merge([
            'type' => 'files',
        ], $config)));
    }
}
