<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;
use Statamic\Exceptions\AuthorizationException;
use Statamic\Facades\Asset;
use Statamic\Facades\Scope;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Resources\CP\Assets\Folder;
use Statamic\Http\Resources\CP\Assets\FolderAsset;
use Statamic\Http\Resources\CP\Assets\SearchedAssetsCollection;
use Statamic\Support\Arr;

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

        $perPage = $request->perPage ?? 15;
        $page = Paginator::resolveCurrentPage();

        $totalAssets = $folder->queryAssets()->count();
        $totalSubfolders = $folder->assetFolders()->count();
        $totalItems = $totalAssets + $totalSubfolders;

        $lastPageShowingSubfolders = (int) ceil($totalSubfolders / $perPage);
        $numberOfSubfoldersOnLastPage = $totalSubfolders % $perPage ?: $perPage;

        $subfolders = $folder->assetFolders()
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        $hasRoomForAssets = ($perPage - $subfolders->count()) > 0;

        if ($hasRoomForAssets) {
            $query = $folder->queryAssets();

            if ($request->sort) {
                $query->orderBy($request->sort, $request->order ?? 'asc');
            } else {
                $query->orderBy($container->sortField(), $container->sortDirection());
            }

            $this->applyQueryScopes($query, $request->all());

            if ($page === $lastPageShowingSubfolders) {
                $offset = 0;
            } else {
                $offset = $perPage * ($page - $lastPageShowingSubfolders) - $numberOfSubfoldersOnLastPage;
            }

            $assets = $query
                ->offset($offset)
                ->limit($perPage - $subfolders->count())
                ->get();
        } else {
            $assets = collect();
        }

        return [
            'data' => [
                'assets' => FolderAsset::collection($assets)->resolve(),
                'folder' => array_merge((new Folder($folder))->resolve(), [
                    'folders' => $subfolders,
                ]),
            ],
            'links' => [
                'asset_action' => cp_route('assets.actions.run'),
                'folder_action' => cp_route('assets.folders.actions.run', $container->id()),
            ],
            'meta' => [
                'current_page' => $page,
                'from' => $totalItems > 0 ? ($page - 1) * $perPage + 1 : null,
                'last_page' => $totalItems > 0 ? max((int) ceil($totalItems / $perPage), 1) : null,
                'path' => Paginator::resolveCurrentPath(),
                'per_page' => $perPage,
                'to' => $totalItems > 0 ? $page * $perPage : null,
                'total' => $totalItems,
            ],
        ];
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

        $this->applyQueryScopes($query, $request->all());

        $assets = $query->paginate(request('perPage'));

        if ($container->hasSearchIndex()) {
            $assets->setCollection($assets->getCollection()->map->getSearchable());
        }

        return new SearchedAssetsCollection($assets);
    }

    protected function applyQueryScopes($query, $params)
    {
        collect(Arr::wrap($params['queryScopes'] ?? null))
            ->map(fn ($handle) => Scope::find($handle))
            ->filter()
            ->each(fn ($scope) => $scope->apply($query, $params));
    }
}
