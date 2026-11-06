<?php

namespace Tests\Console\Commands;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\File;
use Statamic\Facades\Stache;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AssetsMetaCleanTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private ?string $contentMetaDirectory = null;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('test');
        Storage::fake('test_two');
    }

    public function tearDown(): void
    {
        if ($this->contentMetaDirectory) {
            File::delete($this->contentMetaDirectory);
        }

        parent::tearDown();
    }

    private function useMetaAsContent(): string
    {
        config()->set('statamic.assets.meta_as_content', true);

        $this->contentMetaDirectory = sys_get_temp_dir().'/statamic-meta-clean-'.uniqid();

        Stache::store('assets')->directory($this->contentMetaDirectory);

        return $this->contentMetaDirectory;
    }

    #[Test]
    public function dry_run_lists_orphaned_files_without_deleting_them()
    {
        AssetContainer::make('test')->disk('test')->save();

        Storage::disk('test')->put('.meta/root.txt.yaml', 'size: 123');

        $this->artisan('statamic:assets:meta-clean test --dry-run')
            ->expectsOutputToContain('Found 1 orphaned metadata file.')
            ->expectsOutputToContain('[test] .meta/root.txt.yaml');

        $this->assertTrue(Storage::disk('test')->exists('.meta/root.txt.yaml'));
    }

    #[Test]
    public function it_deletes_orphaned_meta_files_and_cleans_up_empty_meta_directories()
    {
        AssetContainer::make('test')->disk('test')->save();

        Storage::disk('test')->put('foo/.meta/bar.txt.yaml', 'size: 123');

        $this->assertTrue(Storage::disk('test')->exists('foo/.meta/bar.txt.yaml'));
        $this->assertTrue(Storage::disk('test')->exists('foo/.meta'));

        $this->artisan('statamic:assets:meta-clean test')
            ->expectsOutputToContain('Deleted 1 orphaned metadata file.');

        $this->assertFalse(Storage::disk('test')->exists('foo/.meta/bar.txt.yaml'));
        $this->assertFalse(Storage::disk('test')->exists('foo/.meta'));
    }

    #[Test]
    public function it_preserves_meta_files_with_matching_assets()
    {
        AssetContainer::make('test')->disk('test')->save();

        Storage::disk('test')->put('foo/bar.txt', 'bar');
        Storage::disk('test')->put('foo/.meta/bar.txt.yaml', 'size: 123');

        $this->artisan('statamic:assets:meta-clean test')
            ->expectsOutputToContain('No orphaned metadata files were found.');

        $this->assertTrue(Storage::disk('test')->exists('foo/.meta/bar.txt.yaml'));
    }

    #[Test]
    public function it_only_cleans_the_requested_container()
    {
        AssetContainer::make('one')->disk('test')->save();
        AssetContainer::make('two')->disk('test_two')->save();

        Storage::disk('test')->put('foo/.meta/one.jpg.yaml', 'size: 1');
        Storage::disk('test_two')->put('foo/.meta/two.jpg.yaml', 'size: 2');

        $this->artisan('statamic:assets:meta-clean one')
            ->expectsOutputToContain('Deleted 1 orphaned metadata file.');

        $this->assertFalse(Storage::disk('test')->exists('foo/.meta/one.jpg.yaml'));
        $this->assertTrue(Storage::disk('test_two')->exists('foo/.meta/two.jpg.yaml'));
    }

    #[Test]
    public function it_cleans_all_containers_when_no_container_argument_is_provided()
    {
        AssetContainer::make('one')->disk('test')->save();
        AssetContainer::make('two')->disk('test_two')->save();

        Storage::disk('test')->put('foo/.meta/one.jpg.yaml', 'size: 1');
        Storage::disk('test_two')->put('foo/.meta/two.jpg.yaml', 'size: 2');

        $this->artisan('statamic:assets:meta-clean')
            ->expectsOutputToContain('Deleted 2 orphaned metadata files.');

        $this->assertFalse(Storage::disk('test')->exists('foo/.meta/one.jpg.yaml'));
        $this->assertFalse(Storage::disk('test_two')->exists('foo/.meta/two.jpg.yaml'));
    }

    #[Test]
    public function it_detects_orphaned_meta_files_in_root_and_nested_meta_directories()
    {
        AssetContainer::make('test')->disk('test')->save();

        Storage::disk('test')->put('.meta/root.jpg.yaml', 'size: 1');
        Storage::disk('test')->put('foo/.meta/nested.jpg.yaml', 'size: 2');

        $this->artisan('statamic:assets:meta-clean test')
            ->expectsOutputToContain('Deleted 2 orphaned metadata files.');

        $this->assertFalse(Storage::disk('test')->exists('.meta/root.jpg.yaml'));
        $this->assertFalse(Storage::disk('test')->exists('foo/.meta/nested.jpg.yaml'));
    }

    #[Test]
    public function dry_run_lists_orphaned_content_meta_files_without_deleting_them()
    {
        $directory = $this->useMetaAsContent();
        AssetContainer::make('test')->disk('test')->save();

        File::put("{$directory}/test/root.txt.yaml", 'size: 123');

        $this->artisan('statamic:assets:meta-clean test --dry-run')
            ->expectsOutputToContain('Found 1 orphaned metadata file.')
            ->expectsOutputToContain('test/root.txt.yaml');

        $this->assertFileExists("{$directory}/test/root.txt.yaml");
    }

    #[Test]
    public function it_deletes_orphaned_content_meta_files_and_keeps_meta_files_with_matching_assets()
    {
        $directory = $this->useMetaAsContent();
        AssetContainer::make('test')->disk('test')->save();

        Storage::disk('test')->put('foo/kept.txt', 'kept');
        File::put("{$directory}/test/foo/kept.txt.yaml", 'size: 1');
        File::put("{$directory}/test/foo/orphan.txt.yaml", 'size: 2');
        File::put("{$directory}/test/bar/baz/orphan.txt.yaml", 'size: 3');

        // Meta files on the disk are not used in this mode, so they must not be touched.
        Storage::disk('test')->put('foo/.meta/disk.txt.yaml', 'size: 4');

        $this->artisan('statamic:assets:meta-clean test')
            ->expectsOutputToContain('Deleted 2 orphaned metadata files.');

        $this->assertFileExists("{$directory}/test/foo/kept.txt.yaml");
        $this->assertFileDoesNotExist("{$directory}/test/foo/orphan.txt.yaml");
        $this->assertFileDoesNotExist("{$directory}/test/bar/baz/orphan.txt.yaml");
        $this->assertDirectoryDoesNotExist("{$directory}/test/bar");
        $this->assertDirectoryExists("{$directory}/test");
        $this->assertTrue(Storage::disk('test')->exists('foo/.meta/disk.txt.yaml'));
    }

    #[Test]
    public function it_finds_nothing_when_the_content_meta_directory_does_not_exist()
    {
        $this->useMetaAsContent();
        AssetContainer::make('test')->disk('test')->save();

        $this->artisan('statamic:assets:meta-clean test')
            ->expectsOutputToContain('No orphaned metadata files were found.');
    }
}
