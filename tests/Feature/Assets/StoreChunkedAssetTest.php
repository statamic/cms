<?php

namespace Tests\Feature\Assets;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\AssetContainer;
use Statamic\Facades;
use Statamic\Http\Middleware\DeleteTemporaryFileUploads;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StoreChunkedAssetTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private $container;

    public function setUp(): void
    {
        parent::setUp();

        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__.'/tmp',
        ]]);

        $this->container = (new AssetContainer)
            ->handle('test_container')
            ->disk('test')
            ->chunkedUploads(true)
            ->save();

        Storage::fake('test');
        Storage::fake('local');
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // Isolate these tests from image preset generation, which decodes the (fake) image bytes.
        $app['config']->set('statamic.assets.image_manipulation.generate_presets_on_upload', false);
    }

    #[Test]
    public function it_uploads_an_asset_in_chunks()
    {
        Storage::disk('test')->assertMissing('path/to/test.jpg');

        $this
            ->actingAs($this->userWithPermission())
            ->uploadChunks('hello chunked world')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => 'test_container::path/to/test.jpg',
                    'path' => 'path/to/test.jpg',
                ],
            ]);

        Storage::disk('test')->assertExists('path/to/test.jpg');
        $this->assertEquals('hello chunked world', Storage::disk('test')->get('path/to/test.jpg'));
        $this->assertEmpty(Storage::disk('local')->allFiles('statamic/chunks'));
    }

    #[Test]
    public function it_appends_a_timestamp_to_a_duplicate_filename()
    {
        Carbon::setTestNow(Carbon::createFromTimestamp(1700000000, config('app.timezone')));
        Storage::disk('test')->put('path/to/test.jpg', 'existing');

        $this
            ->actingAs($this->userWithPermission())
            ->uploadChunks('new contents')
            ->assertOk()
            ->assertJson(['data' => ['path' => 'path/to/test-1700000000.jpg']]);

        $this->assertEquals('existing', Storage::disk('test')->get('path/to/test.jpg'));
        $this->assertEquals('new contents', Storage::disk('test')->get('path/to/test-1700000000.jpg'));
    }

    #[Test]
    public function it_returns_an_acknowledgement_for_intermediate_chunks()
    {
        $this
            ->actingAs($this->userWithPermission())
            ->postChunk('abcde', 0, 2)
            ->assertOk()
            ->assertExactJson(['data' => ['uploadId' => 'testupload123456', 'received' => 0]]);

        Storage::disk('test')->assertMissing('path/to/test.jpg');
    }

    #[Test]
    public function it_denies_access_without_permission()
    {
        $this
            ->actingAs($this->userWithoutPermission())
            ->uploadChunks('hello')
            ->assertStatus(403);
    }

    #[Test]
    public function it_rejects_chunks_when_not_enabled_on_the_container()
    {
        $this->container->chunkedUploads(null)->save();

        $this
            ->actingAs($this->userWithPermission())
            ->uploadChunks('hello')
            ->assertStatus(422);
    }

    #[Test]
    public function it_rejects_a_disallowed_extension_on_the_first_chunk()
    {
        $this
            ->actingAs($this->userWithPermission())
            ->postChunk('hello', 0, 2, ['chunk' => UploadedFile::fake()->createWithContent('virus.exe', 'hello')])
            ->assertStatus(422);

        Storage::disk('local')->assertMissing('statamic/chunks/testupload123456/0');
    }

    #[Test]
    public function it_enforces_the_max_filesize_across_chunks()
    {
        $this->container->validationRules(['max:1'])->save(); // 1 kilobyte

        $this
            ->actingAs($this->userWithPermission())
            ->uploadChunks(str_repeat('a', 2048), 512)
            ->assertStatus(422);

        Storage::disk('test')->assertMissing('path/to/test.jpg');
        $this->assertEmpty(Storage::disk('local')->allFiles('statamic/chunks'));
    }

    #[Test]
    public function it_rejects_an_invalid_upload_id()
    {
        $this
            ->actingAs($this->userWithPermission())
            ->postChunk('hello', 0, 1, ['uploadId' => '../../etc/passwd'])
            ->assertStatus(422);
    }

    #[Test]
    public function it_deletes_chunk_folders_with_no_activity_for_an_hour()
    {
        Carbon::setTestNow();

        Storage::disk('local')->put('statamic/chunks/abandoned/0', 'data');
        Storage::disk('local')->put('statamic/chunks/active/0', 'data');

        // The abandoned upload's newest chunk is over an hour old; the active one was just written.
        touch(Storage::disk('local')->path('statamic/chunks/abandoned/0'), now()->subHours(2)->timestamp);

        (function () {
            $this->deleteAbandonedChunks();
        })->call(new DeleteTemporaryFileUploads);

        Storage::disk('local')->assertMissing('statamic/chunks/abandoned/0');
        Storage::disk('local')->assertExists('statamic/chunks/active/0');
    }

    private function uploadChunks($content, $chunkSize = 5, $overrides = [])
    {
        $chunks = str_split($content, $chunkSize);
        $total = count($chunks);
        $response = null;

        foreach ($chunks as $index => $chunk) {
            $response = $this->postChunk($chunk, $index, $total, $overrides);

            if ($response->getStatusCode() >= 400) {
                break;
            }
        }

        return $response;
    }

    private function postChunk($chunk, $index, $total, $overrides = [])
    {
        return $this->postJson(cp_route('assets.chunks.store'), array_merge([
            'container' => 'test_container',
            'folder' => 'path/to',
            'uploadId' => 'testupload123456',
            'chunkIndex' => $index,
            'totalChunks' => $total,
            'chunk' => UploadedFile::fake()->createWithContent('test.jpg', $chunk),
        ], $overrides));
    }

    private function userWithPermission()
    {
        $this->setTestRoles(['test' => ['access cp', 'upload test_container assets']]);

        return tap(Facades\User::make()->assignRole('test'))->save();
    }

    private function userWithoutPermission()
    {
        $this->setTestRoles(['test' => ['access cp']]);

        return tap(Facades\User::make()->assignRole('test'))->save();
    }
}
