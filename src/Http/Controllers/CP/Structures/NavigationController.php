<?php

namespace Statamic\Http\Controllers\CP\Structures;

use Statamic\Support\Arr;
use Statamic\Facades\Site;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Structure;
use Illuminate\Http\Request;
use Statamic\Contracts\Structures\Nav as NavContract;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Statamic\Structures\TreeBuilder;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Contracts\Structures\Structure as StructureContract;
use Statamic\Facades\Nav;

class NavigationController extends CpController
{
    public function index()
    {
        $this->authorize('index', NavContract::class, 'You are not authorized to view any navs.');

        $navs = Nav::all()->filter(function ($nav) {
            return User::current()->can('view', $nav);
        })->map(function ($structure) {
            $tree = $structure->in(Site::selected()->handle());

            return [
                'id' => $structure->handle(),
                'title' => $structure->title(),
                'show_url' => $tree->showUrl(),
                'edit_url' => $structure->editUrl(),
                'delete_url' => $structure->deleteUrl(),
                'deleteable' => User::current()->can('delete', $structure)
            ];
        })->values();

        return view('statamic::navigation.index', compact('navs'));
    }

    public function edit($nav)
    {
        $nav = Nav::find($nav);

        $this->authorize('edit', $nav, 'You are not authorized to configure navs.');

        $values = [
            'title' => $nav->title(),
            'handle' => $nav->handle(),
            'collections' => $nav->collections()->map->handle()->all(),
            'root' => $nav->expectsRoot(),
            'sites' => Site::all()->map(function ($site) use ($nav) {
                $tree = $nav->in($site->handle());
                return [
                    'name' => $site->name(),
                    'handle' => $site->handle(),
                    'enabled' => $enabled = $tree !== null,
                    'route' => $enabled ? $tree->route() : null,
                    'inherit' => false,
                ];
            })->values()->all()
        ];

        $fields = ($blueprint = $this->editFormBlueprint($nav))
            ->fields()
            ->addValues($values)
            ->preProcess();

        return view('statamic::navigation.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'nav' => $nav,
        ]);
    }

    public function show(Request $request, $nav)
    {
        $nav = Nav::find($nav);

        $site = $request->site ?? Site::selected()->handle();

        if (! $nav || ! $tree = $nav->in($site)) {
            return abort(404);
        }

        return view('statamic::navigation.show', [
            'site' => $site,
            'nav' => $nav,
            'expectsRoot' => $nav->expectsRoot(),
            'collections' => $nav->collections()->map->handle()->all(),
            'localizations' => $nav->sites()->map(function ($handle) use ($nav, $tree) {
                $localized = $nav->in($handle);
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

    public function update(Request $request, $nav)
    {
        $nav = Nav::find($nav);

        $this->authorize('update', $nav, 'You are not authorized to configure navs.');

        $fields = $this->editFormBlueprint()->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values()->all();

        $nav
            ->title($values['title'])
            ->expectsRoot($values['root'])
            ->collections($values['collections'])
            ->maxDepth($values['max_depth']);

        $sites = $values['sites'] ?? [];

        if (!empty($sites)) {
            $nav->sites($sites->filter->enabled->map->handle->values()->all());
        }

        foreach ($sites as $site) {
            $tree = $nav->in($site['handle']);

            if ($tree && !$site['enabled']) {
                $nav->removeTree($tree);
            }

            if (!$tree && $site['enabled']) {
                $tree = $nav->makeTree($site['handle']);
            }

            if (!$site['enabled']) {
                continue;
            }

            $nav->addTree($tree);
        }

        $nav->save();

        return [
            'title' => $nav->title(),
        ];
    }

    public function create()
    {
        $this->authorize('create', NavContract::class, 'You are not authorized to configure navs.');

        return view('statamic::navigation.create');
    }

    public function store(Request $request)
    {
        $this->authorize('store', NavContract::class, 'You are not authorized to configure navs.');

        $values = $request->validate([
            'title' => 'required',
            'handle' => 'required|alpha_dash',
        ]);

        $structure = Nav::make()
            ->title($values['title'])
            ->handle($values['handle']);

        $structure->addTree($structure->makeTree(Site::default()->handle()));

        $structure->save();

        return ['redirect' => $structure->showUrl()];
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
                        'mode' => 'select',
                    ],
                    'root' => [
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

    public function destroy($nav)
    {
        $nav = Nav::findByHandle($nav);

        $this->authorize('delete', $nav, 'You are not authorized to configure navs.');

        $nav->delete();
    }
}
