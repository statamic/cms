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
    public function it_deletes_files_over_an_hour_old_from_the_configured_disk_and_path()
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

        (new \ReflectionMethod(DeleteTemporaryFileUploads::class, 'deleteFilesOverAnHourOld'))
            ->invoke(new DeleteTemporaryFileUploads);

        $uploadsDisk->assertMissing("temp-uploads/{$oldTimestamp}/old.txt");
        $uploadsDisk->assertExists("temp-uploads/{$newTimestamp}/new.txt");
        $localDisk->assertExists("statamic/file-uploads/{$oldTimestamp}/old.txt");
    }
}
