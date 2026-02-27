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
        {--dry-run : List orphaned files without deleting}';

    protected $description = 'Clean orphaned asset metadata files';

    public function handle(): int
    {
        $orphanedMetaFilesByContainer = $this->getContainers()
            ->mapWithKeys(fn ($container) => [$container->handle() => $this->orphanedMetaFiles($container)]);

        $totalOrphanedMetaFiles = $orphanedMetaFilesByContainer->sum->count();

        if ($totalOrphanedMetaFiles === 0) {
            $this->components->info(__('No orphaned metadata files were found.'));

            return self::SUCCESS;
        }

        $flatOrphanedMetaFiles = $orphanedMetaFilesByContainer
            ->flatMap(fn ($paths, $containerHandle) => $paths->map(fn ($path) => [
                'container' => $containerHandle,
                'path' => $path,
            ]))
            ->values();

        if ($this->option('dry-run')) {
            $this->components->warn(__('Found :count orphaned metadata :files.', [
                'count' => $totalOrphanedMetaFiles,
                'files' => Str::plural('file', $totalOrphanedMetaFiles),
            ]));

            $flatOrphanedMetaFiles->each(function ($metaFile) {
                $this->line("[{$metaFile['container']}] {$metaFile['path']}");
            });

            return self::SUCCESS;
        }

        progress(
            label: __('Deleting orphaned asset metadata...'),
            steps: $flatOrphanedMetaFiles,
            callback: function ($metaFile, $progress) {
                /** @var AssetsContainer|null $container */
                $container = AssetContainer::find($metaFile['container']);

                if ($container) {
                    $container->disk()->delete($metaFile['path']);
                }

                $progress->advance();
            }
        );

        $orphanedMetaFilesByContainer->each(function ($metaFiles, $containerHandle) {
            if (! $container = AssetContainer::find($containerHandle)) {
                return;
            }

            $this->deleteEmptyMetaDirectories($container, $metaFiles);
        });

        $this->components->info(__('Deleted :count orphaned metadata :files.', [
            'count' => $totalOrphanedMetaFiles,
            'files' => Str::plural('file', $totalOrphanedMetaFiles),
        ]));

        return self::SUCCESS;
    }

    private function getContainers(): Collection
    {
        if (! $container = $this->argument('container')) {
            return AssetContainer::all();
        }

        return collect([AssetContainer::findOrFail($container)]);
    }

    private function orphanedMetaFiles(AssetsContainer $container): Collection
    {
        $assetPaths = $container->files()->flip();

        return $container->metaFiles()
            ->filter(fn ($path) => Str::endsWith($path, '.yaml'))
            ->filter(fn ($metaPath) => ! $assetPaths->has($this->metaPathToAssetPath($metaPath)))
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
            ->map(fn ($metaFile) => dirname($metaFile))
            ->unique()
            ->sortByDesc(fn ($dir) => substr_count($dir, '/'));

        $metaDirectories->each(function ($metaDirectory) use ($container) {
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
