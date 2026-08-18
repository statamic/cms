<?php

namespace Tests\Forms\Fieldtypes;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\FormUpload;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Upload;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new Upload)->setField(new FormField('avatar', [
            'type' => 'upload',
            'store' => true,
            'container' => 'avatars',
            'folder' => 'uploads',
            'max_files' => 1,
        ]));

        $this->assertEquals([
            'type' => 'form_upload',
            'store' => true,
            'container' => 'avatars',
            'folder' => 'uploads',
            'min_files' => null,
            'max_files' => 1,
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_shows_the_real_filename_for_a_stored_asset()
    {
        Storage::fake('avatars');
        Storage::disk('avatars')->put('avatar.jpg', 'fake-contents');
        AssetContainer::make('avatars')->disk('avatars')->save();
        tap(Asset::make()->container('avatars')->path('avatar.jpg'))->save();

        // Submissions store the bare asset path, not a container-qualified id.
        $field = (new Field('avatar', ['type' => 'form_upload', 'store' => true, 'container' => 'avatars']))
            ->setValue(['avatar.jpg']);

        $fieldtype = (new FormUpload)->setField($field);

        $file = $fieldtype->preload()['files'][0];

        $this->assertEquals('avatar.jpg', $file['filename']);
        $this->assertNotNull($file['download_url']);
        $this->assertNotNull($file['size']);
    }

    #[Test]
    public function it_strips_the_temporary_folder_from_a_temp_files_display_filename()
    {
        $field = (new Field('document', ['type' => 'form_upload', 'store' => false]))
            ->setValue(['1784026119/resume.pdf']);

        $fieldtype = (new FormUpload)->setField($field);

        $this->assertEquals([
            'files' => [
                ['filename' => 'resume.pdf'],
            ],
        ], $fieldtype->preload());
    }
}
