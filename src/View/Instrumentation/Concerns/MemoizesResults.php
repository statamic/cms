<?php

namespace Statamic\View\Instrumentation\Concerns;

use Composer\InstalledVersions;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Statamic\View\Instrumentation\HtmlContext;
use Throwable;

/** @internal */
trait MemoizesResults
{
    protected function flushResultCache(): void
    {
        $this->configurationRevision++;
        $this->cacheScope = null;
    }

    /** @return array<int, HtmlContext|null>|null */
    public static function annotationsFor(string $instrumented): ?array
    {
        if (! static::$transfersContexts || static::$annotations === []) {
            return null;
        }

        return static::$annotations[hash('xxh128', $instrumented)] ?? null;
    }

    public static function transferContexts(bool $enabled = true): void
    {
        static::$transfersContexts = $enabled;
    }

    public static function flushAnnotations(): void
    {
        static::$annotations = [];
    }

    public function lastCacheStoreException(): ?Throwable
    {
        return $this->lastCacheStoreException;
    }

    protected function cachedResult(string $key, bool $persistent = true): ?string
    {
        $entry = static::$results[$this->memoKey($key)] ?? null;

        if ($entry === null && $persistent && ($entry = $this->storedEntry($key)) !== null) {
            $this->memoize($key, $entry);
        }

        if ($entry === null) {
            return null;
        }

        static::rememberAnnotations($entry[2], $entry[1]);

        return $entry[0];
    }

    /** @param  array<int, HtmlContext|null>|null  $contexts */
    protected function storeResult(string $key, string $result, ?array $contexts = null, bool $persistent = true): string
    {
        $entry = [$result, $contexts, hash('xxh128', $result)];

        $this->memoize($key, $entry);

        if ($persistent) {
            $this->persistEntry($key, $entry);
        }

        static::rememberAnnotations($entry[2], $contexts);

        return $result;
    }

    protected function memoKey(string $key): string
    {
        return $this->cacheFingerprint().':'.$key;
    }

    /** @param  array{0: string, 1: array<int, HtmlContext|null>|null, 2: string}  $entry */
    protected function memoize(string $key, array $entry): void
    {
        if (count(static::$results) >= static::$resultLimit) {
            static::$results = array_slice(static::$results, intdiv(static::$resultLimit, 2), null, true);
        }

        static::$results[$this->memoKey($key)] = $entry;
    }

    /** @param  array<int, HtmlContext|null>|null  $contexts */
    protected static function rememberAnnotations(string $annotationKey, ?array $contexts): void
    {
        if ($contexts === null || isset(static::$annotations[$annotationKey])) {
            return;
        }

        if (count(static::$annotations) >= static::$annotationLimit) {
            static::$annotations = array_slice(static::$annotations, intdiv(static::$annotationLimit, 2), null, true);
        }

        static::$annotations[$annotationKey] = $contexts;
    }

    protected function usesCacheStore(): bool
    {
        return $this->cacheScope !== null
            && $this->cacheStore !== false
            && ! $this->cacheStoreFailed;
    }

    protected function cacheRepository(): Repository
    {
        return Cache::store(is_string($this->cacheStore) ? $this->cacheStore : null);
    }

    protected function cacheStoreKey(string $key): string
    {
        return 'statamic.antlers.instrumentation.'.static::cacheStoreNamespace().'.'.$this->cacheScope.'.'.hash('xxh128', $key);
    }

    protected static function cacheStoreNamespace(): string
    {
        return static::$cacheStoreNamespace ??= 'v'.static::CACHE_FORMAT.'.'.hash('xxh64', static::installedVersion());
    }

    protected static function installedVersion(): string
    {
        try {
            if (InstalledVersions::isInstalled('statamic/cms')) {
                $version = InstalledVersions::getVersion('statamic/cms').'@'.InstalledVersions::getReference('statamic/cms');
            } else {
                $root = InstalledVersions::getRootPackage();
                $version = $root['version'].'@'.$root['reference'];
            }
        } catch (Throwable) {
            $version = 'unknown@';
        }

        if (str_contains($version, 'dev') || str_ends_with($version, '@')) {
            $version .= ':'.static::sourceStamp();
        }

        return $version;
    }

    protected static function sourceStamp(): string
    {
        $newestModification = 0;

        foreach (static::analysisSourceDirectories() as $directory) {
            foreach (glob($directory.'/*.php') ?: [] as $file) {
                $newestModification = max($newestModification, (int) filemtime($file));
            }
        }

        return (string) $newestModification;
    }

    /** @return string[] */
    protected static function analysisSourceDirectories(): array
    {
        return [
            __DIR__.'/..',
            __DIR__,
            __DIR__.'/../Antlers',
            __DIR__.'/../../Antlers/Language/Analyzers/Html',
            __DIR__.'/../../Antlers/Language/Analyzers/Html/Concerns',
            __DIR__.'/../../Antlers/Language/Parser',
            __DIR__.'/../../Antlers/Language/Parser/Concerns',
            __DIR__.'/../../Antlers/Language/Nodes',
            __DIR__.'/../../Antlers/Language/Utilities',
        ];
    }

    /** @return array{0: string, 1: array<int, HtmlContext|null>|null, 2: string}|null */
    protected function storedEntry(string $key): ?array
    {
        if (! $this->usesCacheStore()) {
            return null;
        }

        try {
            $stored = $this->cacheRepository()->get($this->cacheStoreKey($key));
        } catch (Throwable $exception) {
            $this->failCacheStore($exception);

            return null;
        }

        if (! is_array($stored) || ! is_string($stored[0] ?? null)) {
            return null;
        }

        $contexts = null;

        if (($stored[1] ?? null) !== null || ($stored[2] ?? null) !== null) {
            $contexts = static::unpackContexts($stored[1] ?? null, $stored[2] ?? null);

            if ($contexts === null) {
                return null;
            }
        }

        return [$stored[0], $contexts, hash('xxh128', $stored[0])];
    }

    /** @param  array{0: string, 1: array<int, HtmlContext|null>|null, 2: string}  $entry */
    protected function persistEntry(string $key, array $entry): void
    {
        if (! $this->usesCacheStore()) {
            return;
        }

        [$stacks, $packed] = static::packContexts($entry[1]);

        try {
            $this->cacheRepository()->put($this->cacheStoreKey($key), [$entry[0], $stacks, $packed], static::CACHE_TTL);
        } catch (Throwable $exception) {
            $this->failCacheStore($exception);
        }
    }

    protected function failCacheStore(Throwable $exception): void
    {
        $this->lastCacheStoreException = $exception;
        $this->cacheStoreFailed = true;
    }

    /**
     * @param  array<int, HtmlContext|null>|null  $contexts
     * @return array{0: array<int, string[]>|null, 1: array<int, array|null>|null}
     */
    protected static function packContexts(?array $contexts): array
    {
        if ($contexts === null) {
            return [null, null];
        }

        $stacks = [];
        $indexes = [];
        $packed = [];

        foreach ($contexts as $context) {
            if ($context === null) {
                $packed[] = null;

                continue;
            }

            $entry = $context->toArray();
            $stackKey = implode('/', $entry[3]);

            if (! isset($indexes[$stackKey])) {
                $indexes[$stackKey] = count($stacks);
                $stacks[] = $entry[3];
            }

            $entry[3] = $indexes[$stackKey];
            $packed[] = $entry;
        }

        return [$stacks, $packed];
    }

    /** @return array<int, HtmlContext|null>|null */
    protected static function unpackContexts(mixed $stacks, mixed $packed): ?array
    {
        if (! is_array($stacks) || ! is_array($packed)) {
            return null;
        }

        $contexts = [];

        try {
            foreach ($packed as $entry) {
                if ($entry === null) {
                    $contexts[] = null;

                    continue;
                }

                if (! static::isPackedContext($entry, $stacks)) {
                    return null;
                }

                $entry[3] = $stacks[$entry[3]];
                $contexts[] = HtmlContext::fromArray($entry);
            }
        } catch (Throwable) {
            return null;
        }

        return $contexts;
    }

    /** @param  array<int, mixed>  $stacks */
    protected static function isPackedContext(mixed $entry, array $stacks): bool
    {
        if (! is_array($entry) || ! array_is_list($entry) || count($entry) !== 6) {
            return false;
        }

        [$kind, $elementName, $attributeName, $stack, $elementStartOffset, $flags] = $entry;

        if (! is_string($kind) || ! is_int($stack) || ! is_int($flags)) {
            return false;
        }

        if (($elementName !== null && ! is_string($elementName))
            || ($attributeName !== null && ! is_string($attributeName))
            || ($elementStartOffset !== null && ! is_int($elementStartOffset))) {
            return false;
        }

        if (! isset($stacks[$stack]) || ! is_array($stacks[$stack]) || ! array_is_list($stacks[$stack])) {
            return false;
        }

        foreach ($stacks[$stack] as $name) {
            if (! is_string($name)) {
                return false;
            }
        }

        return true;
    }
}
