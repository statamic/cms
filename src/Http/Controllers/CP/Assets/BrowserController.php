<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Statamic\Assets\AssetFolder;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;
use Statamic\CP\Column;
use Statamic\Exceptions\AuthorizationException;
use Statamic\Facades\Asset;
use Statamic\Facades\Scope;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Resources\CP\Assets\Folder;
use Statamic\Http\Resources\CP\Assets\FolderAsset;
use Statamic\Http\Resources\CP\Assets\SearchedAssetsCollection;
use Statamic\Http\Resources\CP\Concerns\HasRequestedColumns;
use Statamic\Support\Arr;

class BrowserController extends CpController
{
    use HasRequestedColumns, RedirectsToFirstAssetContainer;

    private $columns;

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

        $this->setColumns($container);

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
            'columns' => $this->columns,
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
        $perPage = $request->perPage ?? config('statamic.cp.pagination_size');
        $page = Paginator::resolveCurrentPage();

        $folders = $folder->assetFolders();
        $totalFolders = $folders->count();
        $lastFolderPage = (int) ceil($totalFolders / $perPage) ?: 1;

        $totalAssets = $folder->queryAssets()->count();
        $totalItems = $totalAssets + $totalFolders;

        if ($request->sort) {
            $sort = $request->sort;
            $order = $request->order ?? 'asc';
        } else {
            $sort = $container->sortField();
            $order = $container->sortDirection();
        }

        $sortByMethod = $order === 'desc' ? 'sortByDesc' : 'sortBy';

        $folders = $folders->$sortByMethod(
            fn (AssetFolder $folder) => method_exists($folder, $sort) ? $folder->$sort() : $folder->basename()
        );

        $folders = $folders->slice(($page - 1) * $perPage, $perPage);

        if ($page >= $lastFolderPage) {
            $query = $folder->queryAssets();
            $query->orderBy($sort, $order);
            $this->applyQueryScopes($query, $request->all());

            $offset = $page === $lastFolderPage
                ? 0
                : $perPage * ($page - $lastFolderPage) - ($totalFolders % $perPage);

            $assets = $query
                ->offset($offset)
                ->limit($perPage - $folders->count())
                ->get();
        }

        $this->setColumns($container);
        $columns = $this->visibleColumns();

        return [
            'data' => [
                'assets' => collect($assets ?? [])
                    ->map(fn ($asset) => (new FolderAsset($asset))
                        ->blueprint($container->blueprint())
                        ->columns($columns)
                        ->resolve()
                    )
                    ->all(),
                'folder' => array_merge((new Folder($folder))->resolve(), [
                    'folders' => Folder::collection($folders->values()),
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
                'columns' => $columns,
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

        if ($request->sort) {
            $query->orderBy($request->sort, $request->order ?? 'asc');
        }

        $this->applyQueryScopes($query, $request->all());

        $assets = $query->paginate(request('perPage'));

        if ($container->hasSearchIndex()) {
            $assets->setCollection($assets->getCollection()->map->getSearchable());
        }

        return (new SearchedAssetsCollection($assets))
            ->blueprint($container->blueprint())
            ->columnPreferenceKey("assets.{$container->handle()}.columns");
    }

    protected function applyQueryScopes($query, $params)
    {
        collect(Arr::wrap($params['queryScopes'] ?? null))
            ->map(fn ($handle) => Scope::find($handle))
            ->filter()
            ->each(fn ($scope) => $scope->apply($query, $params));
    }

    // Duplicated in the SearchedAssetsCollection resource.
    public function setColumns($container)
    {
        $blueprint = $container->blueprint();

        $columns = $blueprint->columns();

        $basename = Column::make('basename')
            ->label(__('File'))
            ->visible(true)
            ->defaultVisibility(true)
            ->sortable(true);

        $size = Column::make('size')
            ->label(__('Size'))
            ->value('size_formatted')
            ->visible(true)
            ->defaultVisibility(true)
            ->sortable(true);

        $lastModified = Column::make('last_modified')
            ->label(__('Last Modified'))
            ->value('last_modified_relative')
            ->visible(true)
            ->defaultVisibility(true)
            ->sortable(true);

        $columns->put('basename', $basename);
        $columns->put('size', $size);
        $columns->put('last_modified', $lastModified);

        $columns->setPreferred("assets.{$container->handle()}.columns");

        $this->columns = $columns->rejectUnlisted()->values();
    }
}
