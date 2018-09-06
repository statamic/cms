<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\AssetContainer;

class AssetContainersController extends CpController
{
    public function index()
    {
        $containers = AssetContainer::all()->filter(function ($container) {
            return true; // TODO: auth.
            // return request()->user()->can('view', $container);
        })->map(function ($container) {
            return [
                'id' => $container->handle(),
                'title' => $container->title(),
                'edit_url' => $container->editUrl(),
                'delete_url' => $container->deleteUrl()
            ];
        })->values();

        return view('statamic::assets.containers.index', [
            'containers' => $containers,
            'columns' => ['title'],
            'visibleColumns' => ['title'],
        ]);
    }

    public function edit($container)
    {
        $container = AssetContainer::find($container);

        // TODO: auth

        return view('statamic::assets.containers.edit', [
            'container' => $container,
            'disks' => $this->disks()
        ]);
    }

    public function update($container)
    {
        $container = AssetContainer::find($container);

        // TODO: auth
        // TODO: validation

        $data = request()->only(['title', 'disk', 'path', 'fieldset']);
        $container->data($data);
        $container->save();

        return back()->with('success', 'Container saved');
    }

    public function create()
    {
        // TODO: auth

        return view('statamic::assets.containers.create', [
            'disks' => $this->disks()
        ]);
    }

    public function store()
    {
        // TODO: auth
        // TODO: validation

        $data = request()->only(['title', 'disk', 'path', 'fieldset']);
        $container = AssetContainer::create();
        $container->handle(request('handle'));
        $container->data($data);
        $container->save();

        return redirect($container->editUrl())->with('success', 'Container saved');
    }

    public function destroy($container)
    {
        $container = AssetContainer::find($container);

        // TODO: auth

        $container->delete();

        return [
            'message' => 'Container deleted',
            'redirect' => cp_route('asset-containers.index')
        ];
    }

    private function disks()
    {
        return collect(config('filesystems.disks'))
            ->keys()
            ->map(function ($disk) {
                return ['text' => $disk, 'value' => $disk];
            });
    }
}
