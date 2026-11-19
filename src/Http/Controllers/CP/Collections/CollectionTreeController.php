<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Statamic\Contracts\Entries\Collection;
use Statamic\Facades\Action;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Structures\TreeBuilder;
use Statamic\Support\Arr;

use function Statamic\trans;

class CollectionTreeController extends CpController
{
    public function index(Request $request, Collection $collection)
    {
        $site = $request->site ?? Site::selected()->handle();

        $pages = (new TreeBuilder)->buildForController([
            'structure' => $collection->structure(),
            'include_home' => true,
            'site' => $site,
        ]);

        if ($collection->structure()->expectsRoot() && ! empty($pages)) {
            $pages[0]['actions'] = Action::for(Entry::find($pages[0]['entry']), ['view' => 'tree']);
        }

        return ['pages' => $pages];
    }

    public function update(Request $request, $collection)
    {
        $this->authorize('reorder', $collection);

        $contents = $this->toTree($request->pages);

        $structure = $collection->structure();
        $tree = $structure->in($request->site);

        // Clone the tree and add the submitted contents into it so we can
        // validate URI uniqueness without affecting the real object in memory.
        $this->validateUniqueUris((clone $tree)->disableUriCache()->tree($contents));

        $this->deleteEntries($request);

        // Validate the tree, which will add any missing entries or throw an exception
        // if somehow the root would end up having child pages, which isn't allowed.
        $contents = $structure->validateTree($contents, $request->site);

        return [
            'saved' => $tree->tree($contents)->save(),
        ];
    }

    private function toTree($items)
    {
        return collect($items)->map(function ($item) {
            return Arr::removeNullValues([
                'entry' => $ref = $item['id'] ?? null,
                'title' => $ref ? null : ($item['title'] ?? null),
                'url' => $ref ? null : ($item['url'] ?? null),
                'children' => $this->toTree($item['children']),
            ]);
        })->all();
    }

    private function validateUniqueUris($tree)
    {
        if (! $tree->collection()->route($tree->locale())) {
            return;
        }

        $siblingUris = [];

        foreach ($tree->diff()->moved() as $id) {
            $page = $tree->find($id);
            $parent = $page->parent();
            $isTopLevel = ! $parent || $parent->isRoot();
            $key = $isTopLevel ? '' : $parent->id();

            // Moved pages under the same parent share siblings, so build that list once.
            $siblingUris[$key] ??= ($isTopLevel ? $tree->pages()->all()->slice(1) : $parent->pages()->all())
                ->mapWithKeys(fn ($sibling) => [$sibling->reference() => $sibling->uri()]);

            // The root is sliced off the top-level siblings, so it won't be in the list.
            $uri = $siblingUris[$key][$id] ?? $page->uri();

            if ($siblingUris[$key]->except($id)->contains($uri)) {
                throw ValidationException::withMessages(['uri' => trans('statamic::validation.duplicate_uri', ['value' => $uri])]);
            }
        }
    }

    private function deleteEntries($request)
    {
        $deletedEntries = collect($request->deletedEntries ?? [])
            ->map(function ($id) {
                return Entry::find($id);
            })
            ->filter(function ($entry) {
                return User::current()->can('delete', $entry);
            });

        if ($request->deleteLocalizationBehavior === 'copy') {
            $deletedEntries->each->detachLocalizations();
        } else {
            $deletedEntries->each->deleteDescendants();
        }

        $deletedEntries->each->delete();
    }
}
