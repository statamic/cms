<?php

namespace Tests\Sidecar;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Entries\Entry;
use Statamic\Facades\Collection as CollectionAPI;
use Statamic\Facades\Entry as EntryAPI;
use Statamic\Facades\Path;
use Statamic\Fields\Blueprint as BlueprintInstance;
use Statamic\Sidecar\Drivers\Driver;
use Statamic\Sidecar\Entries\StoredInNestedFolders;
use Statamic\Sidecar\Manager;
use Statamic\Support\Str;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class NestedFoldersTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private Manager $manager;

    private string $docsDir;

    public function setUp(): void
    {
        parent::setUp();

        $this->docsDir = $this->fakeStacheDirectory.'/nested-docs';
        File::ensureDirectoryExists($this->docsDir);

        $this->manager = new Manager;

        $this->manager->extend('nested', function ($app, $config, $handle) {
            return new class($config, $handle) extends Driver
            {
                public function title(): string
                {
                    return 'Nested Docs';
                }

                public function entryClass(): ?string
                {
                    return NestedFolderEntry::class;
                }

                public function usesNestedFolders(): bool
                {
                    return true;
                }

                public function configure(\Statamic\Entries\Collection $collection): \Statamic\Entries\Collection
                {
                    return parent::configure($collection)
                        ->structureContents(['root' => true])
                        ->sites(['en']);
                }

                protected function defaultBlueprint(): BlueprintInstance
                {
                    return $this->makeBlueprint([
                        'title' => 'Doc',
                        'fields' => [
                            ['handle' => 'content', 'field' => ['type' => 'markdown']],
                        ],
                    ]);
                }

                public function previewUrl(EntryContract $entry): ?string
                {
                    $path = $entry->nestedFolderUriPath();

                    return $path === '' ? url('docs') : url('docs/'.$path);
                }
            };
        });

        $this->app->instance(Manager::class, $this->manager);

        config(['statamic.sidecar.collections' => [
            'docs' => [
                'driver' => 'nested',
                'directory' => $this->docsDir,
            ],
        ]]);

        $this->manager->boot();
    }

    #[Test]
    public function it_builds_paths_from_tree_ancestry()
    {
        $root = $this->makeDoc('index', 'Home', '_index.md');
        $guide = $this->makeDoc('guide', 'Guide', 'guide.md');
        $routing = $this->makeDoc('routing', 'Routing', 'routing.md');

        // expectsRoot: root is tree[0] with no children key; top-level pages follow.
        $this->saveTree([
            ['entry' => $root->id()],
            ['entry' => $guide->id(), 'children' => [
                ['entry' => $routing->id()],
            ]],
        ]);

        $this->assertEquals($this->docsDir.'/_index.md', Path::tidy($root->fresh()->path()));
        $this->assertEquals($this->docsDir.'/guide/_index.md', Path::tidy($guide->fresh()->path()));
        $this->assertEquals($this->docsDir.'/guide/routing.md', Path::tidy($routing->fresh()->path()));

        $this->assertFileExists($this->docsDir.'/_index.md');
        $this->assertFileExists($this->docsDir.'/guide/_index.md');
        $this->assertFileExists($this->docsDir.'/guide/routing.md');
        $this->assertFileDoesNotExist($this->docsDir.'/guide.md');
        $this->assertFileDoesNotExist($this->docsDir.'/routing.md');
    }

    #[Test]
    public function it_converts_section_back_to_leaf_when_last_child_is_removed()
    {
        $root = $this->makeDoc('index', 'Home', '_index.md');
        $guide = $this->makeDoc('guide', 'Guide', 'guide.md');
        $routing = $this->makeDoc('routing', 'Routing', 'routing.md');

        $this->saveTree([
            ['entry' => $root->id()],
            ['entry' => $guide->id(), 'children' => [
                ['entry' => $routing->id()],
            ]],
        ]);

        $this->saveTree([
            ['entry' => $root->id()],
            ['entry' => $guide->id()],
            ['entry' => $routing->id()],
        ]);

        $this->assertEquals($this->docsDir.'/guide.md', Path::tidy($guide->fresh()->path()));
        $this->assertEquals($this->docsDir.'/routing.md', Path::tidy($routing->fresh()->path()));
        $this->assertFileExists($this->docsDir.'/guide.md');
        $this->assertFileDoesNotExist($this->docsDir.'/guide/_index.md');
        $this->assertDirectoryDoesNotExist($this->docsDir.'/guide');
    }

    #[Test]
    public function it_syncs_per_level_order_front_matter()
    {
        $root = $this->makeDoc('index', 'Home', '_index.md');
        $a = $this->makeDoc('alpha', 'Alpha', 'alpha.md');
        $b = $this->makeDoc('bravo', 'Bravo', 'bravo.md');

        $this->saveTree([
            ['entry' => $root->id()],
            ['entry' => $b->id()],
            ['entry' => $a->id()],
        ]);

        // Root is position 1; top-level siblings follow.
        $this->assertEquals(1, $root->fresh()->get('order'));
        $this->assertEquals(2, $b->fresh()->get('order'));
        $this->assertEquals(3, $a->fresh()->get('order'));
    }

    #[Test]
    public function it_hydrates_index_file_slugs_from_folder_names()
    {
        $section = EntryAPI::make()
            ->id('guide-1')
            ->collection('docs')
            ->locale('en')
            ->initialPath($this->docsDir.'/guide/_index.md')
            ->slug('_index')
            ->data(['title' => 'Guide']);

        $root = EntryAPI::make()
            ->id('root-1')
            ->collection('docs')
            ->locale('en')
            ->initialPath($this->docsDir.'/_index.md')
            ->slug('_index')
            ->data(['title' => 'Home']);

        $this->assertInstanceOf(NestedFolderEntry::class, $section);
        $this->assertEquals('guide', $section->slug());
        $this->assertEquals('index', $root->slug());
    }

    #[Test]
    public function new_entries_build_paths_at_the_collection_root()
    {
        $entry = EntryAPI::make()
            ->id('new-1')
            ->collection('docs')
            ->locale('en')
            ->slug('fresh-page')
            ->data(['title' => 'Fresh']);

        $this->assertEquals($this->docsDir.'/fresh-page.md', Path::tidy($entry->buildPath()));
    }

    #[Test]
    public function it_resolves_preview_urls_from_tree_ancestry()
    {
        $root = $this->makeDoc('index', 'Home', '_index.md');
        $guide = $this->makeDoc('guide', 'Guide', 'guide.md');
        $routing = $this->makeDoc('routing', 'Routing', 'routing.md');

        $this->saveTree([
            ['entry' => $root->id()],
            ['entry' => $guide->id(), 'children' => [
                ['entry' => $routing->id()],
            ]],
        ]);

        $this->assertEquals('/docs', $root->fresh()->uri());
        $this->assertEquals('/docs/guide', $guide->fresh()->uri());
        $this->assertEquals('/docs/guide/routing', $routing->fresh()->uri());
    }

    private function makeDoc(string $slug, string $title, string $relativePath): Entry
    {
        $path = $this->docsDir.'/'.$relativePath;
        File::ensureDirectoryExists(dirname($path));

        $entry = EntryAPI::make()
            ->id(Str::uuid()->toString())
            ->collection('docs')
            ->locale('en')
            ->slug($slug)
            ->data(['title' => $title, 'content' => '# '.$title])
            ->published(true);

        // Write via buildPath once so initialPath is set; tree sync relocates later.
        File::put($path, $entry->fileContents());
        $entry->initialPath($path)->saveQuietly();

        return $entry;
    }

    private function saveTree(array $branches): void
    {
        CollectionAPI::find('docs')->structure()->in('en')->tree($branches)->save();
    }
}

class NestedFolderEntry extends Entry
{
    use StoredInNestedFolders;
}
