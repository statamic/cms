<?php

namespace Tests\Feature\Fieldtypes;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FilesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_uploads_a_file()
    {
        $disk = Storage::fake('local');
        $file = UploadedFile::fake()->image('test.jpg');

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->post('/cp/fieldtypes/files/upload', ['file' => $file])
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $path = now()->timestamp.'/test.jpg',
                ],
            ]);

        $disk->assertExists('statamic/file-uploads/'.$path);
    }
}
