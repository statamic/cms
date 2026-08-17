<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Illuminate\Http\Request;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Site;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class TaxonomyTreeController extends CpController
{
    public function index(Request $request, $taxonomy)
    {
        $this->authorize('view', $taxonomy);

        throw_unless($taxonomy->hasStructure(), new NotFoundHttpException("Taxonomy [{$taxonomy->handle()}] is not a structured taxonomy"));

        $site = $request->site ?? Site::selected()->handle();

        if (! $taxonomy->sites()->contains($site)) {
            $site = $taxonomy->sites()->first();
        }

        $pages = $this->transformTree(
            $taxonomy->structure()->tree()->tree(),
            $taxonomy,
            $site
        );

        return ['pages' => $pages];
    }

    public function update(Request $request, $taxonomy)
    {
        $this->authorize('reorder', $taxonomy);

        $this->deleteTerms($request, $taxonomy);

        $structure = $taxonomy->structure();
        $tree = $structure->tree();

        $contents = $structure->validateTree($this->toTree($request->pages), $tree->locale());

        return [
            'saved' => $tree->tree($contents)->save(),
        ];
    }

    private function transformTree($branches, $taxonomy, $site)
    {
        return collect($branches)->map(function ($branch) use ($taxonomy, $site) {
            $slug = $branch['term'] ?? null;

            if (! $slug || ! $term = Term::find($taxonomy->handle().'::'.$slug)) {
                return null;
            }

            $localized = $term->in($site);

            return [
                'id' => $term->id(),
                'title' => null,
                'entry_title' => $localized->title(),
                'url' => $localized->url(),
                'edit_url' => $localized->editUrl(),
                'can_delete' => User::current()->can('delete', $localized),
                'slug' => $localized->slug(),
                'status' => $localized->published() ? 'published' : 'draft',
                'children' => $this->transformTree($branch['children'] ?? [], $taxonomy, $site),
            ];
        })->filter()->values()->all();
    }

    private function toTree($items)
    {
        return collect($items)->map(function ($item) {
            return Arr::removeNullValues([
                'term' => isset($item['id']) ? Str::after($item['id'], '::') : null,
                'children' => $this->toTree($item['children'] ?? []),
            ]);
        })->all();
    }

    private function deleteTerms($request, $taxonomy)
    {
        collect($request->deletedTerms ?? [])
            ->map(fn ($id) => Term::find($taxonomy->handle().'::'.Str::after($id, '::')))
            ->filter(fn ($term) => $term && User::current()->can('delete', $term))
            ->each->delete();
    }
}
