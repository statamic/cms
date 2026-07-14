<?php

namespace Tests\Forms\Fieldtypes;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
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
        Storage::fake('test');
        Storage::disk('test')->put('avatar.jpg', '');
        AssetContainer::make('avatars')->disk('test')->save();

        $field = (new Field('avatar', ['type' => 'form_upload', 'store' => true, 'container' => 'avatars']))
            ->setValue(['avatars::avatar.jpg']);

        $fieldtype = (new FormUpload)->setField($field);

        $files = $fieldtype->preload()['files'];

        $this->assertEquals('avatar.jpg', $files[0]['filename']);
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
