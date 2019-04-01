<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Statamic\API\Arr;
use Illuminate\Http\Request;
use Statamic\API\AssetContainer;
use Statamic\Http\Controllers\CP\CpController;

class AssetContainersController extends CpController
{
    public function index(Request $request)
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

        if ($request->wantsJson()) {
            return $containers;
        }

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

    public function update(Request $request, $container)
    {
        $container = AssetContainer::find($container);

        // TODO: auth

        $request->validate([
            'title' => 'required',
            'disk' => 'required',
            'allow_uploads' => 'boolean',
            'create_folders' => 'boolean',
        ]);

        $container
            ->title($request->title)
            ->disk($request->disk)
            ->blueprint(Arr::first(json_decode($request->blueprint, true)))
            ->allowUploads($request->allow_uploads)
            ->createFolders($request->create_folders)
            ->save();

        return back()->with('success', 'Container saved');
    }

    public function create()
    {
        // TODO: auth

        return view('statamic::assets.containers.create', [
            'disks' => $this->disks()
        ]);
    }

    public function store(Request $request)
    {
        // TODO: auth

        $request->validate([
            'title' => 'required',
            'handle' => 'nullable|alpha_dash',
            'disk' => 'required',
            'allow_uploads' => 'boolean',
            'create_folders' => 'boolean',
        ]);

        $title = $request->title;
        $handle = $request->handle ?? snake_case($title);

        $container = AssetContainer::make($handle)
            ->title($title)
            ->disk($request->disk)
            ->blueprint(Arr::first(json_decode($request->blueprint, true)))
            ->allowUploads($request->allow_uploads)
            ->createFolders($request->create_folders)
            ->save();

        return redirect($container->showUrl())->with('success', 'Container saved');
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
        return collect(config('filesystems.disks'))->keys();
    }
}
