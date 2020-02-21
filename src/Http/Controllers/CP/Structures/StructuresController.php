<?php

namespace Statamic\Http\Controllers\CP\Structures;

use Statamic\Support\Arr;
use Statamic\Facades\Site;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Structure;
use Illuminate\Http\Request;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Statamic\Structures\TreeBuilder;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Contracts\Structures\Structure as StructureContract;

class StructuresController extends CpController
{
    public function index()
    {
        $this->authorize('index', StructureContract::class, 'You are not authorized to view any structures.');

        $structures = Structure::all()->filter(function ($structure) {
            return !$structure->isCollectionBased() && User::current()->can('view', $structure);
        })->map(function ($structure) {
            $tree = $structure->in(Site::selected()->handle());

            return [
                'id' => $structure->handle(),
                'title' => $structure->title(),
                'purpose' => $structure->collection() ? 'collection' : 'navigation',
                'show_url' => $tree->editUrl(),
                'edit_url' => $structure->editUrl(),
                'delete_url' => $structure->deleteUrl(),
                'deleteable' => User::current()->can('delete', $structure)
            ];
        })->values();

        return view('statamic::structures.index', compact('structures'));
    }

    public function edit($structure)
    {
        $structure = Structure::find($structure);

        abort_if($structure->isCollectionBased(), 404);

        $this->authorize('edit', $structure, 'You are not authorized to edit this structure.');

        $values = [
            'title' => $structure->title(),
            'handle' => $structure->handle(),
            'collections' => $structure->collections()->map->handle()->all(),
            'expects_root' => $structure->expectsRoot(),
            'sites' => Site::all()->map(function ($site) use ($structure) {
                $tree = $structure->in($site->handle());
                return [
                    'name' => $site->name(),
                    'handle' => $site->handle(),
                    'enabled' => $enabled = $tree !== null,
                    'route' => $enabled ? $tree->route() : null,
                    'inherit' => false,
                ];
            })->values()->all()
        ];

        $fields = ($blueprint = $this->editFormBlueprint($structure))
            ->fields()
            ->addValues($values)
            ->preProcess();

        return view('statamic::structures.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'structure' => $structure,
        ]);
    }

    public function show(Request $request, $structure)
    {
        $structure = Structure::find($structure);

        abort_if($structure->isCollectionBased(), 404);

        $site = $request->site ?? Site::selected()->handle();

        if (! $structure || ! $tree = $structure->in($site)) {
            return abort(404);
        }

        $blueprints = $structure->isCollectionBased()
            ? $structure->collection()->entryBlueprints()->map(function ($blueprint) {
                return [
                    'handle' => $blueprint->handle(),
                    'title' => $blueprint->title(),
                ];
            }) : collect();

        return view('statamic::structures.show', [
            'site' => $site,
            'structure' => $structure,
            'expectsRoot' => $structure->expectsRoot(),
            'hasCollection' => $structure->isCollectionBased(),
            'collections' => $structure->collections()->map->handle()->all(),
            'collectionBlueprints' => $blueprints,
            'localizations' => $structure->sites()->map(function ($handle) use ($structure, $tree) {
                $localized = $structure->in($handle);
                $exists = $localized !== null;
                if (!$exists) {
                    return null;
                }
                return [
                    'handle' => $handle,
                    'name' => Site::get($handle)->name(),
                    'active' => $handle === $tree->locale(),
                    'exists' => $exists,
                    'url' => $exists ? $localized->editUrl() : null,
                ];
            })->filter()->all()
        ]);
    }

    public function update(Request $request, $structure)
    {
        $fields = $this->editFormBlueprint()->fields()->addValues($request->all());

        $fields->validate();

        $structure = Structure::find($structure);

        abort_if($structure->isCollectionBased(), 404);

        $this->authorize('update', $structure, 'You are not authorized to edit this structure.');

        $expectedRoot = $structure->expectsRoot();

        $values = $fields->process()->values()->all();

        $structure
            ->title($values['title'])
            ->handle($values['handle'])
            ->expectsRoot($expectsRoot = $values['expects_root'])
            ->collections($values['collections'])
            ->maxDepth($values['max_depth']);

        $sites = $values['sites'] ?? [];

        if (!empty($sites)) {
            $structure->sites($sites->filter->enabled->map->handle->values()->all());
        }

        foreach ($sites as $site) {
            $tree = $structure->in($site['handle']);

            if ($tree && !$site['enabled']) {
                $structure->removeTree($tree);
            }

            if (!$tree && $site['enabled']) {
                $tree = $structure->makeTree($site['handle']);
            }

            if (!$site['enabled']) {
                continue;
            }

            $structure->addTree($tree);
        }

        $this->updateRootExpectations($structure, $expectedRoot, $expectsRoot);

        $structure->save();

        return [
            'title' => $structure->title(),
        ];
    }

    public function create()
    {
        $this->authorize('create', Structure::class, 'You are not authorized to create structures.');

        return view('statamic::structures.create');
    }

    public function store(Request $request)
    {
        $this->authorize('store', Structure::class, 'You are not authorized to create structures.');

        $values = $request->validate([
            'title' => 'required',
            'handle' => 'required|alpha_dash',
        ]);

        $structure = Structure::make()
            ->title($values['title'])
            ->handle($values['handle']);

        $structure->addTree($structure->makeTree(Site::default()->handle()));

        $structure->save();

        return ['redirect' => $structure->showUrl()];
    }

    protected function updateRootExpectations($structure, $expected, $expecting)
    {
        if ($expected === $expecting) {
            return;
        }

        $structure->trees()->each(function ($tree) use ($expecting) {
            return $expecting
                ? $this->moveFirstPageToRoot($tree)
                : $this->moveRootToFirstPage($tree);
        });
    }

    protected function moveFirstPageToRoot($tree)
    {
        $arr = $tree->tree();
        $first = Arr::pull($arr, 0);
        $tree
            ->tree(array_values($arr))
            ->root($first['entry']);
    }

    protected function moveRootToFirstPage($tree)
    {
        $root = $tree->root();
        $arr = $tree->tree();
        array_unshift($arr, ['entry' => $root]);
        $tree->root(null)->tree($arr);
    }

    public function editFormBlueprint()
    {
        $contents = [
            'name' => [
                'display' => 'Name',
                'fields' => [
                    'title' => [
                        'type' => 'text',
                        'validate' => 'required',
                    ],
                    'handle' => [
                        'type' => 'slug',
                        'read_only' => true,
                    ],
                ],
            ],
            'options' => [
                'display' => 'Options',
                'fields' => [
                    'collections' => [
                        'type' => 'collections',
                        'display' => 'Collections',
                        'instructions' => 'You will be able to add links to entries from these collections.',
                    ],
                    'expects_root' => [
                        'type' => 'toggle',
                        'display' => 'Expect a root page',
                        'instructions' => 'The first page in the tree should be considered the "root" or "home" page.',
                    ],
                    'max_depth' => [
                        'type' => 'integer',
                        'display' => 'Max depth',
                        'instructions' => 'The maximum number of levels deep a page may be nested. Leave blank for no limit.',
                        'validate' => 'min:0',
                    ],
                ],
            ],
        ];

        if (Site::hasMultiple()) {
            $contents['options']['fields']['sites'] = [
                'type' => 'structure_sites',
                'display' => __('Sites'),
            ];
        };

        return Blueprint::makeFromSections($contents);
    }

    public function destroy($structure)
    {
        $structure = Structure::findByHandle($structure);

        abort_if($structure->isCollectionBased(), 404);

        $this->authorize('delete', $structure, 'You are not authorized to delete this structure.');

        $structure->delete();
    }
}
