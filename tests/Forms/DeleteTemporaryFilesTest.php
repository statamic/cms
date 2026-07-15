<?php

namespace Tests\Forms;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Form;
use Statamic\Forms\DeleteTemporaryFiles;
use Statamic\Forms\Uploaders\FormFileUpload;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DeleteTemporaryFilesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_deletes_the_submissions_whole_temporary_storage_folder()
    {
        Storage::fake('local');

        $form = tap(Form::make('contact')->formFields([
            'sections' => [
                ['fields' => [
                    ['handle' => 'avatar', 'field' => ['type' => 'upload', 'store' => true, 'max_files' => 1]],
                    ['handle' => 'document', 'field' => ['type' => 'upload', 'store' => false, 'max_files' => 1]],
                ]],
            ],
        ]))->save();

        $submission = tap($form->makeSubmission())->save();

        $avatarPath = FormFileUpload::field(['handle' => 'avatar', 'max_files' => 1], $submission->id())
            ->upload([UploadedFile::fake()->image('avatar.jpg')]);
        $documentPath = FormFileUpload::field(['handle' => 'document', 'max_files' => 1], $submission->id())
            ->upload([UploadedFile::fake()->create('resume.pdf', 10)]);

        $submission->set('avatar', $avatarPath)->set('document', $documentPath)->save();

        (new DeleteTemporaryFiles($submission))->handle();

        Storage::disk('local')->assertMissing('statamic/form-uploads/'.$avatarPath);
        Storage::disk('local')->assertMissing('statamic/form-uploads/'.$documentPath);
        Storage::disk('local')->assertDirectoryEmpty('statamic/form-uploads/'.$submission->id());
    }

    #[Test]
    public function it_removes_store_false_field_values_but_keeps_store_true_ones()
    {
        Storage::fake('local');

        $form = tap(Form::make('contact')->formFields([
            'sections' => [
                ['fields' => [
                    ['handle' => 'avatar', 'field' => ['type' => 'upload', 'store' => true, 'max_files' => 1]],
                    ['handle' => 'document', 'field' => ['type' => 'upload', 'store' => false, 'max_files' => 1]],
                ]],
            ],
        ]))->save();

        $submission = tap($form->makeSubmission())->save();

        $documentPath = FormFileUpload::field(['handle' => 'document', 'max_files' => 1], $submission->id())
            ->upload([UploadedFile::fake()->create('resume.pdf', 10)]);

        // Simulate the normal finalize order: `avatar` already promoted to a real asset path by
        // `CreateAssetsFromFileUploads` before this job runs; `document` is still temporary.
        $submission->set('avatar', 'uploads/avatar.jpg')->set('document', $documentPath)->save();

        (new DeleteTemporaryFiles($submission))->handle();

        $this->assertEquals('uploads/avatar.jpg', $form->submission($submission->id())->get('avatar'));
        $this->assertNull($form->submission($submission->id())->get('document'));
    }

    #[Test]
    public function it_does_not_save_when_the_form_does_not_store_submissions()
    {
        Storage::fake('local');

        $form = tap(Form::make('contact')->store(false)->formFields([
            'sections' => [
                ['fields' => [
                    ['handle' => 'document', 'field' => ['type' => 'upload', 'store' => false, 'max_files' => 1]],
                ]],
            ],
        ]))->save();

        $submission = $form->makeSubmission();

        $documentPath = FormFileUpload::field(['handle' => 'document', 'max_files' => 1], $submission->id())
            ->upload([UploadedFile::fake()->create('resume.pdf', 10)]);

        $submission->set('document', $documentPath);

        (new DeleteTemporaryFiles($submission))->handle();

        $this->assertNull($form->submission($submission->id()));
    }

    #[Test]
    public function it_deletes_temp_files_created_by_the_files_fieldtype()
    {
        Storage::fake('local');

        $form = tap(Form::make('contact')->formFields([
            'sections' => [
                ['fields' => [
                    ['handle' => 'document', 'field' => ['type' => 'files']],
                ]],
            ],
        ]))->save();

        $submission = tap($form->makeSubmission())->save();

        $path = now()->timestamp.'/resume.pdf';
        Storage::disk('local')->put('statamic/file-uploads/'.$path, '');
        $submission->set('document', [$path])->save();

        (new DeleteTemporaryFiles($submission))->handle();

        Storage::disk('local')->assertMissing('statamic/file-uploads/'.$path);
        $this->assertNull($form->submission($submission->id())->get('document'));
    }

    #[Test]
    public function it_does_not_delete_asset_files()
    {
        Storage::fake('local');
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->save();

        $form = tap(Form::make('contact')->formFields([
            'sections' => [
                ['fields' => [
                    ['handle' => 'avatar', 'field' => ['type' => 'assets', 'container' => 'avatars']],
                ]],
            ],
        ]))->save();

        $submission = tap($form->makeSubmission()->set('avatar', ['avatar.jpg']))->save();

        (new DeleteTemporaryFiles($submission))->handle();

        $this->assertEquals(['avatar.jpg'], $form->submission($submission->id())->get('avatar'));
    }
}
