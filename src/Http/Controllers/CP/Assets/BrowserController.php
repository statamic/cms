<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Illuminate\Http\Request;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;
use Statamic\Exceptions\AuthorizationException;
use Statamic\Facades\Asset;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Resources\CP\Assets\FolderAssetsCollection;
use Statamic\Http\Resources\CP\Assets\SearchedAssetsCollection;

class BrowserController extends CpController
{
    use RedirectsToFirstAssetContainer;

    public function index()
    {
        $this->redirectToFirstContainer();

        if (User::current()->can('create', AssetContainerContract::class)) {
            return redirect()->cpRoute('assets.index');
        }

        throw new AuthorizationException;
    }

    public function show($container, $path = '/')
    {
        $this->authorize('view', $container);

        return view('statamic::assets.browse', [
            'container' => [
                'id' => $container->id(),
                'title' => $container->title(),
                'edit_url' => $container->editUrl(),
                'delete_url' => $container->deleteUrl(),
                'blueprint_url' => cp_route('asset-containers.blueprint.edit', $container->handle()),
                'can_edit' => User::current()->can('edit', $container),
                'can_delete' => User::current()->can('delete', $container),
                'sort_field' => $container->sortField(),
                'sort_direction' => $container->sortDirection(),
            ],
            'folder' => $path,
        ]);
    }

    public function edit($container, $path)
    {
        $containerHandle = $container->handle();

        $asset = Asset::find("{$containerHandle}::{$path}");

        abort_unless($container && $asset, 404);

        $this->authorize('view', $asset);

        return view('statamic::assets.browse', [
            'container' => [
                'id' => $container->id(),
                'title' => $container->title(),
                'edit_url' => $container->editUrl(),
            ],
            'folder' => $asset->folder(),
            'editing' => $asset->id(),
        ]);
    }

    public function folder(Request $request, $container, $path = '/')
    {
        $this->authorize('view', $container);

        $folder = $container->assetFolder($path);

        $query = $folder->queryAssets();

        if ($request->sort) {
            $query->orderBy($request->sort, $request->order ?? 'asc');
        } else {
            $query->orderBy($container->sortField(), $container->sortDirection());
        }

        $assets = $query->paginate(request('perPage'));

        return (new FolderAssetsCollection($assets))->folder($folder);
    }

    public function search(Request $request, $container, $path = null)
    {
        $this->authorize('view', $container);

        $query = $container->hasSearchIndex()
            ? $container->searchIndex()->ensureExists()->search($request->search)
            : $container->queryAssets()->where('path', 'like', '%'.$request->search.'%');

        if ($path) {
            $query->where('folder', $path);
        }

        $assets = $query->paginate(request('perPage'));

        if ($container->hasSearchIndex()) {
            $assets->setCollection($assets->getCollection()->map->getSearchable());
        }

        return new SearchedAssetsCollection($assets);
    }
}
