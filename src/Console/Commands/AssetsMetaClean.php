<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Statamic\Assets\AssetContainer as AssetsContainer;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\Stache;

use function Laravel\Prompts\progress;

class AssetsMetaClean extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:assets:meta-clean
        { container? : Handle of a container }
        { --dry-run : List orphaned files without deleting }';

    protected $description = 'Clean orphaned asset metadata files';

    public function handle(): int
    {
        $containers = $this->getContainers()->keyBy->handle();

        $orphanedMetaFilesByContainer = $containers->map(fn ($container) => $this->getOrphanedMetaFiles($container));
        $orphanedMetaFilesCount = $orphanedMetaFilesByContainer->sum->count();

        if ($orphanedMetaFilesCount === 0) {
            $this->components->info('No orphaned metadata files were found.');

            return self::SUCCESS;
        }

        $flatOrphanedMetaFiles = $orphanedMetaFilesByContainer
            ->flatMap(fn (Collection $paths, string $container) => $paths->map(fn ($path) => [
                'container' => $container,
                'path' => $path,
            ]))
            ->values();

        if ($this->option('dry-run')) {
            $this->components->warn("Found {$orphanedMetaFilesCount} orphaned metadata ".Str::plural('file', $orphanedMetaFilesCount));

            $flatOrphanedMetaFiles->each(function (array $metaFile) {
                $this->line("[{$metaFile['container']}] {$metaFile['path']}");
            });

            return self::SUCCESS;
        }

        progress(
            label: 'Deleting orphaned asset metadata...',
            steps: $flatOrphanedMetaFiles,
            callback: function (array $metaFile, $progress) use ($containers) {
                $this->metaAsContent()
                    ? File::delete($metaFile['path'])
                    : $containers->get($metaFile['container'])->disk()->delete($metaFile['path']);
                $progress->advance();
            }
        );

        $orphanedMetaFilesByContainer->each(function (Collection $metaFiles, string $container) use ($containers) {
            $this->deleteEmptyMetaDirectories($containers->get($container), $metaFiles);
        });

        $this->components->warn("Deleted {$orphanedMetaFilesCount} orphaned metadata ".Str::plural('file', $orphanedMetaFilesCount));

        return self::SUCCESS;
    }

    private function getContainers(): Collection
    {
        if (! $container = $this->argument('container')) {
            return AssetContainer::all();
        }

        return collect([AssetContainer::findOrFail($container)]);
    }

    private function getOrphanedMetaFiles(AssetsContainer $container): Collection
    {
        $assetPaths = $container->files()->flip();

        return $this->metaFiles($container)
            ->filter(fn (string $path) => Str::endsWith($path, '.yaml'))
            ->reject(fn (string $path) => $assetPaths->has($this->metaPathToAssetPath($container, $path)))
            ->values();
    }

    private function metaAsContent(): bool
    {
        return (bool) config('statamic.assets.meta_as_content');
    }

    private function contentMetaDirectory(AssetsContainer $container): string
    {
        return Path::tidy(Stache::store('assets')->directory().$container->handle());
    }

    private function metaFiles(AssetsContainer $container): Collection
    {
        if (! $this->metaAsContent()) {
            return $container->metaFiles();
        }

        $directory = $this->contentMetaDirectory($container);

        if (! File::isDirectory($directory)) {
            return collect();
        }

        return File::withAbsolutePaths()->getFilesRecursively($directory)->map(fn ($path) => Path::tidy($path))->values();
    }

    private function metaPathToAssetPath(AssetsContainer $container, string $metaPath): string
    {
        if ($this->metaAsContent()) {
            return Str::of($metaPath)
                ->after($this->contentMetaDirectory($container).'/')
                ->beforeLast('.yaml')
                ->value();
        }

        $pathWithoutYamlExtension = Str::endsWith($metaPath, '.yaml')
            ? substr($metaPath, 0, -5)
            : $metaPath;

        $pathWithoutMetaDirectory = str_replace('/.meta/', '/', $pathWithoutYamlExtension);

        if (Str::startsWith($pathWithoutMetaDirectory, '.meta/')) {
            $pathWithoutMetaDirectory = Str::replaceFirst('.meta/', '', $pathWithoutMetaDirectory);
        }

        return ltrim($pathWithoutMetaDirectory, '/');
    }

    private function deleteEmptyMetaDirectories(AssetsContainer $container, Collection $metaFiles): void
    {
        if ($this->metaAsContent()) {
            $this->deleteEmptyContentMetaDirectories($container, $metaFiles);

            return;
        }

        $metaDirectories = $metaFiles
            ->map(fn (string $metaFile) => dirname($metaFile))
            ->unique()
            ->sortByDesc(fn (string $directory) => substr_count($directory, '/'));

        $metaDirectories->each(function (string $metaDirectory) use ($container) {
            $disk = $container->disk();

            if (! $disk->exists($metaDirectory)) {
                return;
            }

            if ($disk->isEmpty($metaDirectory)) {
                $disk->filesystem()->deleteDirectory($metaDirectory);
            }
        });
    }

    private function deleteEmptyContentMetaDirectories(AssetsContainer $container, Collection $metaFiles): void
    {
        $root = $this->contentMetaDirectory($container);

        $metaFiles->each(function (string $metaFile) use ($root) {
            $directory = dirname($metaFile);

            // Walk up the mirrored folder tree, but never remove the container's own directory.
            while (Str::startsWith($directory, $root.'/') && File::isDirectory($directory) && File::isEmpty($directory)) {
                File::delete($directory);
                $directory = dirname($directory);
            }
        });
    }
}
