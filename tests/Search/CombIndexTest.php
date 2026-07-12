<?php

namespace Tests\Search;

use Illuminate\Contracts\Filesystem\Filesystem;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Search\Searchable;
use Statamic\Search\Comb\Index;
use Tests\TestCase;

class CombIndexTest extends TestCase
{
    use IndexTests {
        tearDown as protected indexTestsTearDown;
    }

    private $fs;

    private ?string $tmpDir = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->fs = Mockery::mock(Filesystem::class);
        $this->fs->shouldReceive('disk')->andReturn(Mockery::self());
        $this->instance('filesystem', $this->fs);
    }

    public function tearDown(): void
    {
        if ($this->tmpDir && is_dir($this->tmpDir)) {
            foreach (glob($this->tmpDir.'/*') as $file) {
                @unlink($file);
            }
            @rmdir($this->tmpDir);
        }

        $this->indexTestsTearDown();
    }

    protected function beforeSearched()
    {
        $this->fs
            ->shouldReceive('exists')
            ->with('local/storage/search/test.json')
            ->andReturn(true);

        $this->fs
            ->shouldReceive('get')
            ->with('local/storage/search/test.json')
            ->andReturn('[[]]');
    }

    public function getIndexClass()
    {
        return Index::class;
    }

    #[Test]
    public function delete_does_not_rewrite_the_index_when_the_reference_is_absent()
    {
        $path = $this->createIndexFile(['entry::a' => ['title' => 'Foo']]);

        $oldMtime = time() - 3600;
        touch($path, $oldMtime);
        clearstatcache();

        $index = $this->getIndex('test', ['path' => $this->tmpDir], null);

        $document = Mockery::mock(Searchable::class);
        $document->shouldReceive('getSearchReference')->andReturn('entry::missing');

        $index->delete($document);

        clearstatcache();
        $this->assertSame(
            $oldMtime,
            filemtime($path),
            'Comb\Index::delete() rewrote the index file even though the reference was not present.'
        );
    }

    #[Test]
    public function delete_rewrites_the_index_when_the_reference_is_present()
    {
        $path = $this->createIndexFile([
            'entry::a' => ['title' => 'Foo'],
            'entry::b' => ['title' => 'Bar'],
        ]);

        $oldMtime = time() - 3600;
        touch($path, $oldMtime);
        clearstatcache();

        $index = $this->getIndex('test', ['path' => $this->tmpDir], null);

        $document = Mockery::mock(Searchable::class);
        $document->shouldReceive('getSearchReference')->andReturn('entry::a');

        $index->delete($document);

        clearstatcache();
        $this->assertGreaterThan(
            $oldMtime,
            filemtime($path),
            'Comb\Index::delete() did not rewrite the index file even though the reference was present.'
        );

        $this->assertSame(
            ['entry::b' => ['title' => 'Bar']],
            json_decode(file_get_contents($path), true)
        );
    }

    private function createIndexFile(array $data): string
    {
        $this->tmpDir = sys_get_temp_dir().'/statamic_comb_test_'.uniqid();
        mkdir($this->tmpDir, 0777, true);
        $path = $this->tmpDir.'/test.json';
        file_put_contents($path, json_encode($data));

        return $path;
    }
}
