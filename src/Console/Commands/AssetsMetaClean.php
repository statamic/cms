<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Statamic\Assets\AssetContainer as AssetsContainer;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\AssetContainer;

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
                $containers->get($metaFile['container'])->disk()->delete($metaFile['path']);
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

        return $container->metaFiles()
            ->filter(fn (string $path) => Str::endsWith($path, '.yaml'))
            ->reject(fn (string $path) => $assetPaths->has($this->metaPathToAssetPath($path)))
            ->values();
    }

    private function metaPathToAssetPath(string $metaPath): string
    {
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
}
