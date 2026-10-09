<?php

namespace Tests\Forms;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Form;
use Statamic\Forms\CreateAssetsFromFileUploads;
use Statamic\Forms\Uploaders\FormFileUpload;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CreateAssetsFromFileUploadsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_creates_an_asset_from_a_temporary_file()
    {
        Storage::fake('local');
        $this->fakeAvatarsContainer();

        $form = tap(Form::make('contact')->formFields([
            'sections' => [
                ['fields' => [
                    ['handle' => 'avatar', 'field' => ['type' => 'upload', 'store' => true, 'container' => 'avatars', 'max_files' => 1]],
                ]],
            ],
        ]))->save();

        $submission = tap($form->makeSubmission())->save();

        $path = FormFileUpload::field(['handle' => 'avatar', 'max_files' => 1], $submission->id())
            ->upload([UploadedFile::fake()->image('avatar.jpg')]);

        $submission->set('avatar', $path)->save();

        (new CreateAssetsFromFileUploads($submission))->handle();

        Storage::disk('local')->assertMissing('statamic/form-uploads/'.$path);
        Storage::disk('avatars')->assertExists('avatar.jpg');

        $newValue = $form->submission($submission->id())->get('avatar');
        $this->assertIsString($newValue);
        $this->assertNotEquals($path, $newValue);
        $this->assertNotNull(Asset::find("avatars::{$newValue}"));
    }

    #[Test]
    public function it_creates_an_asset_from_a_temporary_file_on_the_configured_disk_and_path()
    {
        config([
            'statamic.system.file_uploads_disk' => 'uploads',
            'statamic.forms.file_uploads_path' => 'temp-form-uploads',
        ]);

        Storage::fake('local');
        $uploadsDisk = Storage::fake('uploads');
        $this->fakeAvatarsContainer();

        $form = tap(Form::make('contact')->formFields([
            'sections' => [
                ['fields' => [
                    ['handle' => 'avatar', 'field' => ['type' => 'upload', 'store' => true, 'container' => 'avatars', 'max_files' => 1]],
                ]],
            ],
        ]))->save();

        $submission = tap($form->makeSubmission())->save();

        $path = FormFileUpload::field(['handle' => 'avatar', 'max_files' => 1], $submission->id())
            ->upload([UploadedFile::fake()->image('avatar.jpg')]);

        $submission->set('avatar', $path)->save();

        (new CreateAssetsFromFileUploads($submission))->handle();

        $uploadsDisk->assertMissing('temp-form-uploads/'.$path);
        Storage::disk('avatars')->assertExists('avatar.jpg');

        $this->assertNotNull(Asset::find('avatars::'.$form->submission($submission->id())->get('avatar')));
    }

    #[Test]
    public function it_creates_an_asset_from_a_temporary_file_on_a_non_local_disk()
    {
        $root = sys_get_temp_dir().'/statamic-non-local-disk-test-'.uniqid();

        Storage::extend('memory', fn ($app, $config) => new NonLocalPathFilesystemAdapter(
            new Filesystem($adapter = new LocalFilesystemAdapter($root)), $adapter, $config
        ));
        config(['filesystems.disks.memory' => ['driver' => 'memory']]);
        config(['statamic.system.file_uploads_disk' => 'memory']);

        $this->fakeAvatarsContainer();

        $form = tap(Form::make('contact')->formFields([
            'sections' => [
                ['fields' => [
                    ['handle' => 'avatar', 'field' => ['type' => 'upload', 'store' => true, 'container' => 'avatars', 'max_files' => 1]],
                ]],
            ],
        ]))->save();

        $submission = tap($form->makeSubmission())->save();

        $path = FormFileUpload::field(['handle' => 'avatar', 'max_files' => 1], $submission->id())
            ->upload([UploadedFile::fake()->image('avatar.jpg')]);

        $submission->set('avatar', $path)->save();

        (new CreateAssetsFromFileUploads($submission))->handle();

        Storage::disk('avatars')->assertExists('avatar.jpg');

        $newValue = $form->submission($submission->id())->get('avatar');
        $this->assertIsString($newValue);
        $this->assertNotEquals($path, $newValue);
        $this->assertNotNull(Asset::find("avatars::{$newValue}"));

        File::deleteDirectory($root);
    }

    #[Test]
    public function it_creates_assets_from_multiple_temporary_files()
    {
        Storage::fake('local');
        $this->fakeAvatarsContainer();

        $form = tap(Form::make('contact')->formFields([
            'sections' => [
                ['fields' => [
                    ['handle' => 'photos', 'field' => ['type' => 'upload', 'store' => true, 'container' => 'avatars', 'max_files' => 3]],
                ]],
            ],
        ]))->save();

        $submission = tap($form->makeSubmission())->save();

        $paths = FormFileUpload::field(['handle' => 'photos', 'max_files' => 3], $submission->id())
            ->upload([UploadedFile::fake()->image('one.jpg'), UploadedFile::fake()->image('two.jpg')]);

        $submission->set('photos', $paths)->save();

        (new CreateAssetsFromFileUploads($submission))->handle();

        Storage::disk('avatars')->assertExists('one.jpg');
        Storage::disk('avatars')->assertExists('two.jpg');

        $newValue = $form->submission($submission->id())->get('photos');
        $this->assertCount(2, $newValue);
        $this->assertNotNull(Asset::find("avatars::{$newValue[0]}"));
        $this->assertNotNull(Asset::find("avatars::{$newValue[1]}"));
    }

    #[Test]
    public function it_only_creates_assets_when_storage_is_enabled_on_upload_field()
    {
        Storage::fake('local');
        $this->fakeAvatarsContainer();

        $form = tap(Form::make('contact')->formFields([
            'sections' => [
                ['fields' => [
                    ['handle' => 'avatar', 'field' => ['type' => 'upload', 'store' => true, 'container' => 'avatars', 'max_files' => 1]],
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

        (new CreateAssetsFromFileUploads($submission))->handle();

        // `avatar` (store: true) is promoted to a real asset...
        Storage::disk('local')->assertMissing('statamic/form-uploads/'.$avatarPath);
        Storage::disk('avatars')->assertExists('avatar.jpg');
        $newAvatarValue = $form->submission($submission->id())->get('avatar');
        $this->assertNotEquals($avatarPath, $newAvatarValue);
        $this->assertNotNull(Asset::find("avatars::{$newAvatarValue}"));

        // ...but `document` (store: false) is left exactly as it was.
        Storage::disk('local')->assertExists('statamic/form-uploads/'.$documentPath);
        $this->assertEquals($documentPath, $form->submission($submission->id())->get('document'));
    }

    #[Test]
    public function it_skips_creating_an_asset_when_the_temporary_file_doesnt_exist()
    {
        Storage::fake('local');
        $this->fakeAvatarsContainer();

        $form = tap(Form::make('contact')->formFields([
            'sections' => [
                ['fields' => [
                    ['handle' => 'avatar', 'field' => ['type' => 'upload', 'store' => true, 'container' => 'avatars', 'max_files' => 1]],
                ]],
            ],
        ]))->save();

        $submission = tap($form->makeSubmission()->set('avatar', 'never-existed.jpg'))->save();

        (new CreateAssetsFromFileUploads($submission))->handle();

        $this->assertEquals('never-existed.jpg', $form->submission($submission->id())->get('avatar'));
        $this->assertEmpty(AssetContainer::find('avatars')->assets());
    }

    #[Test]
    public function it_never_reads_or_deletes_outside_the_submissions_own_directory()
    {
        Storage::fake('local');
        $this->fakeAvatarsContainer();

        $form = tap(Form::make('contact')->formFields([
            'sections' => [
                ['fields' => [
                    ['handle' => 'avatar', 'field' => ['type' => 'upload', 'store' => true, 'container' => 'avatars', 'max_files' => 1]],
                ]],
            ],
        ]))->save();

        Storage::disk('local')->put('framework/sessions/secret', 'sensitive');

        $submission = tap($form->makeSubmission()->set('avatar', '../../framework/sessions/secret'))->save();

        (new CreateAssetsFromFileUploads($submission))->handle();

        Storage::disk('local')->assertExists('framework/sessions/secret');
        $this->assertEmpty(AssetContainer::find('avatars')->assets());
    }

    #[Test]
    public function it_still_creates_the_asset_when_the_form_does_not_store_submissions()
    {
        Storage::fake('local');
        $this->fakeAvatarsContainer();

        $form = tap(Form::make('contact')->store(false)->formFields([
            'sections' => [
                ['fields' => [
                    ['handle' => 'avatar', 'field' => ['type' => 'upload', 'store' => true, 'container' => 'avatars', 'max_files' => 1]],
                ]],
            ],
        ]))->save();

        $submission = $form->makeSubmission();

        $path = FormFileUpload::field(['handle' => 'avatar', 'max_files' => 1], $submission->id())
            ->upload([UploadedFile::fake()->image('avatar.jpg')]);

        $submission->set('avatar', $path);

        (new CreateAssetsFromFileUploads($submission))->handle();

        // The asset is still created and the temporary copy still cleaned up...
        Storage::disk('avatars')->assertExists('avatar.jpg');
        Storage::disk('local')->assertMissing('statamic/form-uploads/'.$path);

        // ...but since this form never persists submissions, there's nothing to save it to.
        $this->assertNull($form->submission($submission->id()));
        $this->assertNotEquals($path, $submission->get('avatar'));
    }

    private function fakeAvatarsContainer(): void
    {
        Storage::fake('avatars');
        AssetContainer::make('avatars')->disk('avatars')->save();
    }
}
