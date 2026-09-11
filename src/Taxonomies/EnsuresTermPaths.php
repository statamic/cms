<?php

namespace Statamic\Taxonomies;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Statamic\Contracts\Taxonomies\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Support\Str;

use function Statamic\trans as __;

class EnsuresTermPaths
{
    /**
     * The character that separates segments in a typed term path, e.g. "animals > cat".
     */
    const DELIMITER = '>';

    /**
     * Association/lookup slug for a stored value. The delimiter is an input
     * convention for the CP terms field only — in a stored value it's an
     * ordinary character, so the whole value is slugified either way.
     */
    public function slugFromValue(mixed $value, ?string $language = null): string
    {
        return Str::slug((string) $value, '-', $language ?? 'en');
    }

    /**
     * Split a typed path into its non-empty segments, e.g. "animals > cat".
     */
    public function segments(string $value): Collection
    {
        return collect(explode(self::DELIMITER, $value))
            ->map(fn ($segment) => trim($segment))
            ->filter()
            ->values();
    }

    /**
     * Create any missing segments of a nested path and graft them into the
     * taxonomy tree. Returns the leaf slug, or null if creation was refused.
     *
     * Existing segments are reused in place — a term that's already somewhere
     * in the tree is never re-parented.
     */
    public function ensure(Taxonomy $taxonomy, string $value, ?string $language = null, ?Closure $canCreate = null, string $errorKey = 'path'): ?string
    {
        if (! $taxonomy->hierarchical() || ! str_contains($value, self::DELIMITER)) {
            return $this->slugFromValue($value, $language);
        }

        $segments = $this->segments($value);

        if ($segments->isEmpty()) {
            return null;
        }

        $resolved = $segments->map(fn ($segment) => [
            'title' => $segment,
            'slug' => Str::slug($segment, '-', $language ?? 'en'),
        ]);

        $this->assertPathFitsWithinMaxDepth($taxonomy, $resolved->pluck('slug')->all(), $value, $errorKey);

        $missing = $resolved->filter(
            fn ($segment) => ! Term::find($taxonomy->handle().'::'.$segment['slug'])
        );

        if ($missing->isNotEmpty() && $canCreate && ! $canCreate()) {
            return null;
        }

        foreach ($missing as $segment) {
            Term::make()
                ->slug($segment['slug'])
                ->taxonomy($taxonomy)
                ->set('title', $segment['title'])
                ->save();
        }

        $parentSlug = null;
        $slug = null;
        $grafted = false;

        foreach ($resolved as $segment) {
            $slug = $segment['slug'];

            if ($parentSlug) {
                $taxonomy->structure()->graftTerm($slug, $parentSlug, save: false);
                $grafted = true;
            }

            $parentSlug = $slug;
        }

        if ($grafted) {
            $taxonomy->structure()->tree()->save();
        }

        return $slug;
    }

    /**
     * Reject a path whose leaf would land deeper than the taxonomy allows, before
     * anything is created. Counting the typed segments isn't enough: segments
     * already in the tree are reused where they sit, so a two segment path
     * grafted onto an already nested term can still land too deep.
     */
    private function assertPathFitsWithinMaxDepth(Taxonomy $taxonomy, array $slugs, string $path, string $errorKey): void
    {
        $structure = $taxonomy->structure();

        if (! $max = $structure->maxDepth()) {
            return;
        }

        $depth = 0;

        foreach ($slugs as $slug) {
            // An existing term is never re-parented, so it keeps its own depth.
            // Anything else gets grafted under the previous segment.
            $depth = $structure->depthOfTerm($slug) ?? $depth + 1;

            if ($depth > $max) {
                throw ValidationException::withMessages([
                    $errorKey => __('statamic::validation.term_path_exceeds_max_depth', [
                        'path' => $path,
                        'max' => $max,
                    ]),
                ]);
            }
        }
    }
}
