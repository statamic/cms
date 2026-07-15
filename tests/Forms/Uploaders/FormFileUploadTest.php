<?php

namespace Tests\Forms\Uploaders;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Uploaders\FormFileUpload;
use Tests\TestCase;

class FormFileUploadTest extends TestCase
{
    #[Test]
    public function it_writes_a_single_file_to_the_submissions_temporary_storage_folder()
    {
        Storage::fake('local');

        $path = FormFileUpload::field(['handle' => 'document', 'max_files' => 1], 'submission-123')
            ->upload([UploadedFile::fake()->create('resume.pdf', 10)]);

        $this->assertEquals('submission-123/document/resume.pdf', $path);
        Storage::disk('local')->assertExists('statamic/form-uploads/submission-123/document/resume.pdf');
    }

    #[Test]
    public function it_writes_multiple_files_and_returns_an_array_of_paths()
    {
        Storage::fake('local');

        $paths = FormFileUpload::field(['handle' => 'documents', 'max_files' => 3], 'submission-123')
            ->upload([
                UploadedFile::fake()->create('one.pdf', 10),
                UploadedFile::fake()->create('two.pdf', 10),
            ]);

        $this->assertEquals([
            'submission-123/documents/one.pdf',
            'submission-123/documents/two.pdf',
        ], $paths);

        Storage::disk('local')->assertExists('statamic/form-uploads/submission-123/documents/one.pdf');
        Storage::disk('local')->assertExists('statamic/form-uploads/submission-123/documents/two.pdf');
    }

    #[Test]
    public function it_only_takes_the_first_file_when_the_field_is_single_file()
    {
        Storage::fake('local');

        $path = FormFileUpload::field(['handle' => 'document', 'max_files' => 1], 'submission-123')
            ->upload([
                UploadedFile::fake()->create('one.pdf', 10),
                UploadedFile::fake()->create('two.pdf', 10),
            ]);

        $this->assertEquals('submission-123/document/one.pdf', $path);
        Storage::disk('local')->assertMissing('statamic/form-uploads/submission-123/document/two.pdf');
    }

    #[Test]
    public function different_fields_on_the_same_submission_get_their_own_folder()
    {
        Storage::fake('local');

        FormFileUpload::field(['handle' => 'avatar', 'max_files' => 1], 'submission-123')
            ->upload([UploadedFile::fake()->image('avatar.jpg')]);

        FormFileUpload::field(['handle' => 'document', 'max_files' => 1], 'submission-123')
            ->upload([UploadedFile::fake()->create('resume.pdf', 10)]);

        Storage::disk('local')->assertExists('statamic/form-uploads/submission-123/avatar/avatar.jpg');
        Storage::disk('local')->assertExists('statamic/form-uploads/submission-123/document/resume.pdf');
    }

    #[Test]
    public function it_keeps_files_with_the_same_name_by_suffixing_duplicates()
    {
        Storage::fake('local');

        $paths = FormFileUpload::field(['handle' => 'documents', 'max_files' => 3], 'submission-123')
            ->upload([
                UploadedFile::fake()->create('resume.pdf', 10),
                UploadedFile::fake()->create('resume.pdf', 10),
                UploadedFile::fake()->create('resume.pdf', 10),
            ]);

        $this->assertEquals([
            'submission-123/documents/resume.pdf',
            'submission-123/documents/resume-1.pdf',
            'submission-123/documents/resume-2.pdf',
        ], $paths);

        Storage::disk('local')->assertExists('statamic/form-uploads/submission-123/documents/resume.pdf');
        Storage::disk('local')->assertExists('statamic/form-uploads/submission-123/documents/resume-1.pdf');
        Storage::disk('local')->assertExists('statamic/form-uploads/submission-123/documents/resume-2.pdf');
    }
}
