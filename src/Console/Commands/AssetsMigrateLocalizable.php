<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;

class AssetsMigrateLocalizable extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:assets:migrate-localizable
        {container? : Handle of a container}
        {--site= : Root site handle to receive existing data}';

    protected $description = 'Migrate asset metadata to localizable structure';

    public function handle()
    {
        $rootSite = $this->option('site') ?: Site::default()->handle();
        $siteOrigins = $this->siteOrigins($rootSite);
        $containers = $this->containers();

        $fileMigrated = 0;
        $fileScanned = 0;

        foreach ($containers as $container) {
            if (! $container->localizable()) {
                $this->components->warn(__('Skipping [:container] because localizable metadata is disabled.', [
                    'container' => $container->handle(),
                ]));

                continue;
            }

            foreach ($container->metaFiles() as $metaPath) {
                $fileScanned++;
                $contents = $container->disk()->get($metaPath);
                $meta = $contents ? YAML::file($metaPath)->parse($contents) : [];
                $normalized = $this->normalizeMeta($meta, $siteOrigins, $rootSite);

                if ($normalized === $meta) {
                    continue;
                }

                $container->disk()->put($metaPath, YAML::dump($normalized));
                $fileMigrated++;
            }
        }

        $localizableHandles = $containers
            ->filter(fn ($c) => $c->localizable())
            ->map(fn ($c) => $c->handle())
            ->values()
            ->all();

        [$dbMigrated, $dbScanned] = $this->migrateEloquentMeta($siteOrigins, $rootSite, $localizableHandles);

        $this->components->info(__('Migrated :migrated of :scanned asset metadata files.', [
            'migrated' => $fileMigrated,
            'scanned' => $fileScanned,
        ]));

        if ($dbScanned > 0) {
            $this->components->info(__('Migrated :migrated of :scanned Eloquent asset metadata rows.', [
                'migrated' => $dbMigrated,
                'scanned' => $dbScanned,
            ]));
            $this->components->warn(__('For future optimization, add a dedicated locale column in the eloquent-driver assets table.'));
        }

        return self::SUCCESS;
    }

    protected function containers()
    {
        if (! $handle = $this->argument('container')) {
            return AssetContainer::all();
        }

        $container = AssetContainer::find($handle);

        if (! $container) {
            throw new \InvalidArgumentException(__('Invalid container [:container].', ['container' => $handle]));
        }

        return collect([$container]);
    }

    protected function siteOrigins(string $rootSite): array
    {
        return Site::all()->mapWithKeys(function ($site) use ($rootSite) {
            return [$site->handle() => $site->handle() === $rootSite ? null : $rootSite];
        })->all();
    }

    protected function normalizeMeta(array $meta, array $siteOrigins, string $rootSite): array
    {
        $data = $meta['data'] ?? [];

        if (! is_array($data)) {
            $data = [];
        }

        if (! $this->isLocalizedData($data, $siteOrigins)) {
            $data = [$rootSite => $data];
        }

        $data = collect($data)
            ->map(fn ($localeData) => array_filter(($localeData ?? []), fn ($value) => ! is_null($value)))
            ->filter(fn ($localeData) => ! empty($localeData))
            ->all();

        $resolvedSiteOrigins = $this->siteOriginsForMeta($meta, $siteOrigins);

        if ($this->siteOriginsAreDefault($resolvedSiteOrigins)) {
            unset($meta['sites']);
        } else {
            $meta['sites'] = $resolvedSiteOrigins;
        }

        $meta['data'] = $data;

        return $meta;
    }

    protected function isLocalizedData(array $data, array $siteOrigins): bool
    {
        if (empty($data)) {
            return false;
        }

        $siteHandles = array_keys($siteOrigins);

        return collect(array_keys($data))->every(fn ($key) => in_array($key, $siteHandles));
    }

    protected function defaultSiteOrigins(): array
    {
        $default = Site::default()->handle();

        return Site::all()->mapWithKeys(function ($site) use ($default) {
            return [$site->handle() => $site->handle() === $default ? null : $default];
        })->all();
    }

    protected function siteOriginsForMeta(array $meta, array $fallbackSiteOrigins): array
    {
        $sites = $meta['sites'] ?? null;

        if (! is_array($sites)) {
            return $fallbackSiteOrigins;
        }

        return collect($fallbackSiteOrigins)->mapWithKeys(function ($origin, $site) use ($sites) {
            return [$site => array_key_exists($site, $sites) ? $sites[$site] : $origin];
        })->all();
    }

    protected function siteOriginsAreDefault(array $siteOrigins): bool
    {
        return $siteOrigins === $this->defaultSiteOrigins();
    }

    protected function migrateEloquentMeta(array $siteOrigins, string $rootSite, array $localizableContainerHandles): array
    {
        $modelClass = app()->bound('statamic.eloquent.assets.model')
            ? app('statamic.eloquent.assets.model')
            : null;

        if (! $modelClass) {
            return [0, 0];
        }

        $table = (new $modelClass)->getTable();

        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'meta')) {
            return [0, 0];
        }

        if (empty($localizableContainerHandles)) {
            return [0, 0];
        }

        if (! Schema::hasColumn($table, 'container')) {
            $this->components->warn(__('Skipping Eloquent migration: assets table has no container column. Add one to filter by container.'));

            return [0, 0];
        }

        $migrated = 0;
        $scanned = 0;

        $query = DB::table($table)
            ->select(['id', 'meta'])
            ->whereIn('container', $localizableContainerHandles)
            ->orderBy('id');

        $query->lazy()->each(function ($row) use ($table, $siteOrigins, $rootSite, &$migrated, &$scanned) {
            $scanned++;
            $meta = is_array($row->meta) ? $row->meta : (json_decode($row->meta ?? '{}', true) ?: []);
            $normalized = $this->normalizeMeta($meta, $siteOrigins, $rootSite);

            if ($normalized === $meta) {
                return;
            }

            DB::table($table)->where('id', $row->id)->update([
                'meta' => $normalized,
                'updated_at' => now(),
            ]);

            $migrated++;
        });

        return [$migrated, $scanned];
    }
}
