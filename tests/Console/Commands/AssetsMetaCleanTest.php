<?php

namespace Tests\Console\Commands;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AssetsMetaCleanTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('test');
        Storage::fake('test_two');
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
}
