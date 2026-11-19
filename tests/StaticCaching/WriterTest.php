<?php

namespace Tests\StaticCaching;

use PHPUnit\Framework\Attributes\Test;
use Statamic\StaticCaching\Cachers\Writer;
use Tests\TestCase;

class WriterTest extends TestCase
{
    private string $path;

    public function setUp(): void
    {
        parent::setUp();

        $this->path = storage_path('framework/testing/static-cache-writer/page.html');

        @unlink($this->path);
    }

    public function tearDown(): void
    {
        @unlink($this->path);
        @rmdir(dirname($this->path));

        parent::tearDown();
    }

    #[Test]
    public function it_writes_the_content_to_disk()
    {
        $written = (new Writer)->write($this->path, '<html>hello</html>');

        $this->assertTrue($written);
        $this->assertSame('<html>hello</html>', file_get_contents($this->path));
    }

    #[Test]
    public function it_truncates_stale_bytes_when_overwriting_with_shorter_content()
    {
        $writer = new Writer;

        $writer->write($this->path, '<html>this is a much longer page</html>');
        $writer->write($this->path, '<html>short</html>');

        $this->assertSame('<html>short</html>', file_get_contents($this->path));
    }
}
