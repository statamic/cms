<?php

namespace Tests\Assets;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\ChunkUploads;
use Tests\TestCase;

class ChunkUploadsTest extends TestCase
{
    #[Test]
    public function it_caps_the_chunk_size_to_the_configured_max()
    {
        config(['statamic.assets.chunked_upload.chunk_overhead' => 0]);
        config(['statamic.assets.chunked_upload.max_chunk_size' => 5 * 1024 * 1024]);

        $this->assertEquals(min(ChunkUploads::phpMaxUploadSize(), 5 * 1024 * 1024), ChunkUploads::chunkSize());
        $this->assertLessThanOrEqual(5 * 1024 * 1024, ChunkUploads::chunkSize());
    }

    #[Test]
    public function it_reads_the_max_filesize_from_validation_rules()
    {
        $this->assertEquals(5242880, ChunkUploads::maxFilesizeBytes(['max_filesize:5120']));
        $this->assertEquals(5242880, ChunkUploads::maxFilesizeBytes(['mimes:jpg', 'max:5120']));
        $this->assertEquals(1048576, ChunkUploads::maxFilesizeBytes(['max:5120', 'max_filesize:1024']));
        $this->assertNull(ChunkUploads::maxFilesizeBytes(['mimes:jpg']));
        $this->assertNull(ChunkUploads::maxFilesizeBytes([]));
        $this->assertNull(ChunkUploads::maxFilesizeBytes(null));
    }
}
