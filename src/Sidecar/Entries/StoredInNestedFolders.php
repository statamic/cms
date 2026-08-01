<?php

namespace Statamic\Sidecar\Entries;

use Closure;
use Statamic\Facades\Path;
use Statamic\Facades\Sidecar;
use Statamic\Support\Str;

/**
 * Entry path/slug behavior for Sidecar drivers that store nesting as real
 * subfolders on disk, driven by the collection structure tree.
 *
 * - Slugs are plain filenames (never contain `/`).
 * - Structure root → `{dir}/_index.md`
 * - Page with children → `{dir}/{ancestry}/{slug}/_index.md`
 * - Leaf → `{dir}/{ancestry}/{slug}.md`
 *
 * @experimental
 */
trait StoredInNestedFolders
{
    public function slug($slug = null)
    {
        if (func_num_args() === 0) {
            return $this->resolveNestedFolderSlug();
        }

        if ($slug instanceof Closure) {
            $this->slug = $slug;

            return $this;
        }

        if (is_string($slug) && $slug === $this->nestedFolderIndexName() && $this->initialPath()) {
            $slug = $this->slugDerivedFromIndexPath() ?? $slug;
        }

        $this->slug = $slug;

        return $this;
    }

    public function buildPath()
    {
        $directory = rtrim($this->collection()->resolvedDirectory(), '/');

        return Path::tidy($directory.'/'.$this->nestedFolderRelativePath().'.'.$this->fileExtension());
    }

    /**
     * URI path segments for public URLs (empty string for the structure root).
     */
    public function nestedFolderUriPath(): string
    {
        if ($this->isStructureRoot()) {
            return '';
        }

        $segments = $this->nestedFolderAncestrySegments();
        $segments[] = $this->slug() ?? $this->id();

        return implode('/', array_filter($segments, fn ($s) => $s !== null && $s !== ''));
    }

    /**
     * Relative path key without extension (e.g. `guide/routing`, `guide/_index`).
     */
    public function nestedFolderPathKey(): ?string
    {
        if ($relative = $this->relativePathFromInitialWithoutExtension()) {
            return $relative;
        }

        return $this->nestedFolderRelativePath();
    }

    protected function resolveNestedFolderSlug(): ?string
    {
        $slug = $this->slug;

        if ($slug instanceof Closure) {
            $this->slug = null;
            $slug = $slug($this);
            $this->slug = $slug;
        }

        if (! $slug) {
            return null;
        }

        $lang = method_exists($this, 'site') ? $this->site()->lang() : null;

        return Str::slug($slug, '-', $lang);
    }

    protected function nestedFolderRelativePath(): string
    {
        $index = $this->nestedFolderIndexName();

        if ($this->isStructureRoot()) {
            return $index;
        }

        $segments = $this->nestedFolderAncestrySegments();
        $slug = $this->slug() ?? $this->id();

        if ($this->hasNestedFolderChildren()) {
            $segments[] = $slug;
            $segments[] = $index;
        } else {
            $segments[] = $slug;
        }

        return implode('/', $segments);
    }

    protected function nestedFolderAncestrySegments(): array
    {
        if ($page = $this->page()) {
            $segments = [];
            $parent = $page->parent();

            while ($parent && ! $parent->isRoot()) {
                array_unshift($segments, $parent->slug());
                $parent = $parent->parent();
            }

            return array_values(array_filter($segments, fn ($s) => filled($s)));
        }

        return $this->ancestryFromInitialPath();
    }

    protected function ancestryFromInitialPath(): array
    {
        if (! $relative = $this->relativePathFromInitialWithoutExtension()) {
            return [];
        }

        $index = $this->nestedFolderIndexName();

        if ($relative === $index) {
            return [];
        }

        if (Str::endsWith($relative, '/'.$index)) {
            $parts = explode('/', Str::beforeLast($relative, '/'.$index));
            array_pop($parts);

            return array_values(array_filter($parts));
        }

        $parts = explode('/', $relative);
        array_pop($parts);

        return array_values(array_filter($parts));
    }

    protected function hasNestedFolderChildren(): bool
    {
        if ($page = $this->page()) {
            return $page->pages()->all()->isNotEmpty();
        }

        if (! $relative = $this->relativePathFromInitialWithoutExtension()) {
            return false;
        }

        $index = $this->nestedFolderIndexName();

        return $relative !== $index && Str::endsWith($relative, '/'.$index);
    }

    protected function isStructureRoot(): bool
    {
        // Tree::find() skips the expectsRoot page, so detect via the tree root branch.
        if ($this->id() && ($structure = $this->structure()) && $structure->expectsRoot()) {
            $root = $structure->in($this->locale())?->root();

            if (($root['entry'] ?? null) === $this->id()) {
                return true;
            }
        }

        $relative = $this->relativePathFromInitialWithoutExtension();

        return $relative === $this->nestedFolderIndexName();
    }

    protected function slugDerivedFromIndexPath(): ?string
    {
        $relative = $this->relativePathFromInitialWithoutExtension();

        if (! $relative) {
            return null;
        }

        $index = $this->nestedFolderIndexName();

        if ($relative === $index) {
            return 'index';
        }

        if (Str::endsWith($relative, '/'.$index)) {
            return basename(Str::beforeLast($relative, '/'.$index));
        }

        return null;
    }

    protected function relativePathFromInitialWithoutExtension(): ?string
    {
        if (! $this->initialPath() || ! $this->collection()) {
            return null;
        }

        $directory = Path::tidy(Str::finish($this->collection()->resolvedDirectory(), '/'));
        $initial = Path::tidy($this->initialPath());

        if (! Str::startsWith($initial, $directory)) {
            return null;
        }

        $relative = Str::after($initial, $directory);

        return Str::beforeLast($relative, '.'.$this->fileExtension()) ?: null;
    }

    protected function nestedFolderIndexName(): string
    {
        $handle = $this->collectionHandle();

        if ($handle && Sidecar::manages($handle)) {
            return Sidecar::driver($handle)->indexFileName();
        }

        return '_index';
    }
}
