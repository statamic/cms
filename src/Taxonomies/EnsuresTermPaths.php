<?php

namespace Statamic\Taxonomies;

use Closure;
use Illuminate\Validation\ValidationException;
use Statamic\Contracts\Taxonomies\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Support\Str;

use function Statamic\trans as __;

class EnsuresTermPaths
{
    /**
     * Association/lookup slug for an entry value. Nested paths like
     * "events/concerts" resolve to the leaf slug ("concerts") when the
     * taxonomy is hierarchical; otherwise the whole value is slugified.
     */
    public function slugFromValue(mixed $value, ?string $language = null, bool $hierarchical = false): string
    {
        $value = (string) $value;

        if ($hierarchical && str_contains($value, '/')) {
            $value = collect(explode('/', $value))
                ->map(fn ($segment) => trim($segment))
                ->filter()
                ->last() ?? $value;
        }

        return Str::slug($value, '-', $language ?? 'en');
    }

    /**
     * Create any missing segments of a nested path and graft them into the
     * taxonomy tree. Returns the leaf slug, or null if creation was refused.
     *
     * Existing segments are reused in place — a term that's already somewhere
     * in the tree is never re-parented.
     */
    public function ensure(Taxonomy $taxonomy, string $value, ?string $language = null, ?Closure $canCreate = null): ?string
    {
        if (! $taxonomy->hierarchical() || ! str_contains($value, '/')) {
            return $this->slugFromValue($value, $language);
        }

        $segments = collect(explode('/', $value))
            ->map(fn ($segment) => trim($segment))
            ->filter()
            ->values();

        if ($segments->isEmpty()) {
            return null;
        }

        if (($maxDepth = $taxonomy->structure()->maxDepth()) && $segments->count() > $maxDepth) {
            throw ValidationException::withMessages([
                'path' => __('statamic::validation.term_path_exceeds_max_depth', [
                    'path' => $value,
                    'max' => $maxDepth,
                ]),
            ]);
        }

        $resolved = $segments->map(fn ($segment) => [
            'title' => $segment,
            'slug' => Str::slug($segment, '-', $language ?? 'en'),
        ]);

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
}
