<?php

namespace Tests\Http\Middleware;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Http\Middleware\DeleteTemporaryFileUploads;
use Tests\TestCase;

class DeleteTemporaryFileUploadsTest extends TestCase
{
    #[Test]
    public function it_deletes_file_uploads_over_an_hour_old_from_the_configured_disk_and_path()
    {
        config([
            'statamic.system.file_uploads_disk' => 'uploads',
            'statamic.system.file_uploads_path' => 'temp-uploads',
        ]);

        $localDisk = Storage::fake('local');
        $uploadsDisk = Storage::fake('uploads');

        Date::setTestNow(now());

        $oldTimestamp = now()->subHours(2)->timestamp;
        $newTimestamp = now()->timestamp;

        $uploadsDisk->put("temp-uploads/{$oldTimestamp}/old.txt", 'old');
        $uploadsDisk->put("temp-uploads/{$newTimestamp}/new.txt", 'new');
        $localDisk->put("statamic/file-uploads/{$oldTimestamp}/old.txt", 'old');

        $this->deleteTemporaryFileUploads('temp-uploads', now()->subHour());

        $uploadsDisk->assertMissing("temp-uploads/{$oldTimestamp}/old.txt");
        $uploadsDisk->assertExists("temp-uploads/{$newTimestamp}/new.txt");
        $localDisk->assertExists("statamic/file-uploads/{$oldTimestamp}/old.txt");
    }

    #[Test]
    public function it_deletes_form_uploads_over_a_week_old_from_the_configured_disk_and_path()
    {
        config([
            'statamic.system.file_uploads_disk' => 'uploads',
            'statamic.forms.file_uploads_path' => 'temp-form-uploads',
        ]);

        $localDisk = Storage::fake('local');
        $uploadsDisk = Storage::fake('uploads');

        Date::setTestNow(now());

        $oldTimestamp = now()->subWeeks(2)->timestamp;
        $newTimestamp = now()->subDay()->timestamp;

        $uploadsDisk->put("temp-form-uploads/{$oldTimestamp}/old.txt", 'old');
        $uploadsDisk->put("temp-form-uploads/{$newTimestamp}/new.txt", 'new');
        $localDisk->put("statamic/form-uploads/{$oldTimestamp}/old.txt", 'old');

        $this->deleteTemporaryFileUploads('temp-form-uploads', now()->subWeek());

        $uploadsDisk->assertMissing("temp-form-uploads/{$oldTimestamp}/old.txt");
        $uploadsDisk->assertExists("temp-form-uploads/{$newTimestamp}/new.txt");
        $localDisk->assertExists("statamic/form-uploads/{$oldTimestamp}/old.txt");
    }

    private function deleteTemporaryFileUploads(string $directory, $olderThan): void
    {
        (new \ReflectionMethod(DeleteTemporaryFileUploads::class, 'deleteTemporaryFileUploads'))
            ->invoke(new DeleteTemporaryFileUploads, $directory, $olderThan);
    }
}
