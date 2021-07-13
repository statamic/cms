<?php

namespace Statamic\Http\Controllers\CP\Navigation;

use Illuminate\Http\Request;
use Statamic\Facades\Nav;
use Statamic\Facades\Site;
use Statamic\Fields\Blueprint;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Structures\TreeBuilder;
use Statamic\Support\Arr;

class NavigationTreeController extends CpController
{
    private $data;

    public function index(Request $request, $handle)
    {
        $nav = Nav::find($handle);

        $site = $request->site ?? Site::selected()->handle();

        $nav->in($site)->ensureBranchIds();

        $pages = (new TreeBuilder)->buildForController([
            'structure' => $nav,
            'include_home' => true,
            'site' => $site,
        ]);

        return ['pages' => $pages];
    }

    public function update(Request $request, $nav)
    {
        $nav = Nav::find($nav);

        $tree = $nav->in($request->site);

        $this->data = $this->flattenExistingBranchData([], $tree->tree());

        $blueprint = $nav->blueprint()
            ->ensureField('title', ['type' => 'text'])
            ->ensureField('url', ['type' => 'text']);

        $this->updateData($request->data, $blueprint);

        $tree = $this->reorderTree($request->pages);

        $nav->in($request->site)->tree($tree)->save();
    }

    private function updateData(array $data, Blueprint $blueprint)
    {
        collect($data)->each(function ($branch, $id) use ($blueprint) {
            $data = $blueprint->fields()
                ->addValues($branch['values'])
                ->process()
                ->values()
                ->only($branch['localizedFields']);

            $this->data[$id] = Arr::removeNullValues([
                'id' => $id,
                'entry' => $branch['entry'] ?? null,
                'title' => $data->pull('title'),
                'url' => $data->pull('url'),
                'data' => $data->all(),
            ]);
        });
    }

    private function flattenExistingBranchData($data, $branches)
    {
        foreach ($branches as $branch) {
            $data[$branch['id']] = Arr::except($branch, 'children');

            if ($children = $branch['children'] ?? false) {
                $data = $data + $this->flattenExistingBranchData($data, $children);
            }
        }

        return $data;
    }

    private function reorderTree($tree)
    {
        return collect($tree)->map(function ($branch) {
            $item = $this->data[$branch['id']];

            if ($children = $branch['children']) {
                $item['children'] = $this->reorderTree($children);
            }

            return $item;
        })->all();
    }
}
