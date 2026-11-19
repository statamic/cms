<?php

namespace Tests\Search;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blink;
use Statamic\Facades\Search;
use Statamic\Facades\Stache;
use Statamic\Facades\YAML;
use Statamic\Search\InsertMultipleJob;
use Tests\Fakes\CountingFilesystemAdapter;
use Tests\Fakes\ImageFixtures;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @see https://github.com/statamic/cms/issues/14061 */
class InsertMultipleJobAssetsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private const SIZE = 1024 * 1024;
    private const PATHS = ['one.jpg', 'two.jpg', 'three.png', 'four.png'];

    private CountingFilesystemAdapter $adapter;
    private string $searchPath;

    public function setUp(): void
    {
        parent::setUp();

        $this->adapter = new CountingFilesystemAdapter;
        $this->adapter->seed('one.jpg', ImageFixtures::jpeg(30, 60, self::SIZE));
        $this->adapter->seed('two.jpg', ImageFixtures::jpeg(30, 60, self::SIZE));
        $this->adapter->seed('three.png', ImageFixtures::png(30, 60, self::SIZE));
        $this->adapter->seed('four.png', ImageFixtures::png(30, 60, self::SIZE));

        Storage::set('remote', new FilesystemAdapter(new Filesystem($this->adapter), $this->adapter, []));

        AssetContainer::make('remote')->disk('remote')->save();

        $this->searchPath = sys_get_temp_dir().'/statamic-search-'.uniqid();

        config([
            'statamic.search.drivers.local.path' => $this->searchPath,
            'statamic.search.indexes.default' => [
                'driver' => 'local',
                'searchables' => 'assets:remote',
                'fields' => ['title'],
            ],
        ]);
    }

    public function tearDown(): void
    {
        array_map('unlink', glob($this->searchPath.'/*') ?: []);
        @rmdir($this->searchPath);

        parent::tearDown();
    }

    #[Test]
    public function it_does_not_download_whole_assets_to_index_them()
    {
        $this->runJob();

        $this->assertIndexed(self::PATHS);
        $this->assertSame(4, $this->adapter->objectReads);
        $this->assertLessThan(self::SIZE / 2, $this->adapter->objectBytes);
    }

    #[Test]
    public function it_does_not_read_assets_that_already_have_meta()
    {
        foreach (self::PATHS as $path) {
            $this->adapter->seed(".meta/{$path}.yaml", YAML::dump([
                'data' => [],
                'size' => self::SIZE,
                'last_modified' => 1700000000,
                'width' => 30,
                'height' => 60,
                'mime_type' => 'image/jpeg',
                'duration' => null,
            ]));
        }

        $this->runJob();

        $this->assertIndexed(self::PATHS);
        $this->assertSame(0, $this->adapter->objectReads);
        $this->assertSame(0, $this->adapter->heads);
        $this->assertSame(0, $this->adapter->writes);
    }

    #[Test]
    public function it_picks_up_where_it_left_off_when_retried()
    {
        $this->adapter->failAfterObjectReads(2);

        try {
            $this->runJob();
            $this->fail('The job should have been interrupted.');
        } catch (RuntimeException $e) {
            $this->assertSame('Simulated worker timeout.', $e->getMessage());
        }

        // A retry happens in a new process, which keeps the cache and nothing else.
        Blink::flush();
        Stache::clear();
        $this->adapter->reset();

        $this->runJob();

        $this->assertIndexed(self::PATHS);
        $this->assertSame(2, $this->adapter->objectReads);
    }

    #[Test]
    public function it_reads_nothing_when_run_again()
    {
        $this->runJob();

        Blink::flush();
        Stache::clear();
        $this->adapter->reset();

        $this->runJob();

        $this->assertSame(0, $this->adapter->objectReads);
        $this->assertSame(0, $this->adapter->heads);
        $this->assertSame(0, $this->adapter->writes);
    }

    private function runJob(): void
    {
        $documents = collect(self::PATHS)->map(fn ($path) => "asset::remote::{$path}");

        (new InsertMultipleJob('default', null, $documents))->handle();
    }

    private function assertIndexed(array $paths): void
    {
        $indexed = json_decode(Search::index('default')->raw(), true);

        $this->assertEqualsCanonicalizing(
            array_map(fn ($path) => "asset::remote::{$path}", $paths),
            array_keys($indexed)
        );
    }
}
