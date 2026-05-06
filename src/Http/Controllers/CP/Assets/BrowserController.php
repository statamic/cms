<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Inertia\Inertia;
use Statamic\Assets\AssetFolder;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;
use Statamic\CP\Column;
use Statamic\Exceptions\AuthorizationException;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Asset;
use Statamic\Facades\Scope;
use Statamic\Facades\User;
use Statamic\Hooks\CP\AssetsIndexQuery;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Http\Resources\CP\Assets\Folder;
use Statamic\Http\Resources\CP\Assets\FolderAsset;
use Statamic\Http\Resources\CP\Assets\SearchedAssetsCollection;
use Statamic\Http\Resources\CP\Concerns\HasRequestedColumns;
use Statamic\Query\OrderBy;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;
use Statamic\Support\Arr;

class BrowserController extends CpController
{
    use HasRequestedColumns, QueriesFilters, RedirectsToFirstAssetContainer;

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

        return Inertia::render('assets/Browse', [
            ...$this->browseData($container, $path),
            'editing' => null,
        ]);
    }

    public function edit($container, $path)
    {
        $containerHandle = $container->handle();

        $asset = Asset::find("{$containerHandle}::{$path}");

        throw_unless($container && $asset, new NotFoundHttpException);

        $this->authorize('view', $asset);

        $this->setColumns($container);

        return Inertia::render('assets/Browse', [
            ...$this->browseData($container, $asset->folder()),
            'editing' => $asset->id(),
        ]);
    }

    protected function browseData($container, $path)
    {
        return [
            'container' => [
                'id' => $container->id(),
                'title' => $container->title(),
                'edit_url' => $container->editUrl(),
                'delete_url' => $container->deleteUrl(),
                'blueprint_url' => cp_route('blueprints.asset-containers.edit', $container->handle()),
                'can_edit' => User::current()->can('edit', $container),
                'can_delete' => User::current()->can('delete', $container),
                'can_upload' => User::current()->can('store', [\Statamic\Contracts\Assets\Asset::class, $container]),
                'can_create_folders' => User::current()->can('create', [\Statamic\Contracts\Assets\AssetFolder::class, $container]),
                'sort_field' => $container->sortField(),
                'sort_direction' => $container->sortDirection(),
            ],
            'folder' => $path,
            'columns' => $this->columns,
            'filters' => Scope::filters('assets', ['container' => $container->handle(), 'folder' => $path]),
            'canCreateContainers' => User::current()->can('create', \Statamic\Contracts\Assets\AssetContainer::class),
            'createContainerUrl' => cp_route('asset-containers.create'),
        ];
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

        $query = (new AssetsIndexQuery($folder->queryAssets(), $container))->query();
        $totalAssets = $query->count();
        $totalItems = $totalAssets + $totalFolders;

        if ($sort = OrderBy::column($request->sort)) {
            $order = $request->order ?? 'asc';
        } else {
            $sort = $container->sortField();
            $order = $container->sortDirection();
        }

        $sortByMethod = $order === 'desc' ? 'sortByDesc' : 'sortBy';

        $folders = $folders->$sortByMethod(
            fn (AssetFolder $folder) => in_array($sort, ['basename', 'title', 'lastModified', 'size', 'count', 'path']) ? $folder->$sort() : $folder->basename()
        );

        $folders = $folders->slice(($page - 1) * $perPage, $perPage);

        if ($page >= $lastFolderPage) {
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
            'data' => collect($assets ?? [])
                ->map(fn ($asset) => (new FolderAsset($asset))
                    ->blueprint($container->blueprint())
                    ->columns($columns)
                    ->resolve()
                )
                ->all(),
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
                'folder' => array_merge((new Folder($folder))->resolve(), [
                    'folders' => Folder::collection($folders->values()),
                ]),
            ],
        ];
    }

    /**
     * Search and filter through all assets in the container, ignoring folders.
     * This is used for the global search and when listing filters are applied.
     */
    public function search(FilteredRequest $request, $container, $path = null)
    {
        $this->authorize('view', $container);

        if ($request->search && $container->hasSearchIndex()) {
            $query = $container->searchIndex()->ensureExists()->search($request->search);
        } elseif ($request->search) {
            $query = $container->queryAssets()->where('path', 'like', '%'.$request->search.'%');
        } else {
            $query = $container->queryAssets();
        }

        if ($path) {
            $query->where('folder', $path);
        }

        $query = (new AssetsIndexQuery($query, $container))->query();

        if ($sort = OrderBy::column($request->sort)) {
            $query->orderBy($sort, $request->order ?? 'asc');
        }

        $this->applyQueryScopes($query, $request->all());

        $activeFilterBadges = $this->queryFilters($query, $request->filters, [
            'container' => $container->handle(),
            'folder' => $path,
        ]);

        $assets = $query->paginate(request('perPage'));

        if ($request->search && $container->hasSearchIndex()) {
            $assets->setCollection($assets->getCollection()->map->getSearchable());
        }

        return (new SearchedAssetsCollection($assets))
            ->blueprint($container->blueprint())
            ->columnPreferenceKey("assets.{$container->handle()}.columns")
            ->additional([
                'meta' => [
                    'activeFilterBadges' => $activeFilterBadges,
                ],
            ]);
    }

    protected function applyQueryScopes($query, $params)
    {
        collect(Arr::wrap($params['queryScopes'] ?? null))
            ->map(fn ($handle) => Scope::find($handle))
            ->filter()
            ->each(fn ($scope) => $scope->apply($query, $params));
    }

    public function setColumns($container)
    {
        $blueprint = $container->blueprint();

        $columns = $blueprint->columns();

        $basename = Column::make('basename')
            ->label(__('File'))
            ->visible(true)
            ->defaultVisibility(true)
            ->sortable(true)
            ->required(true);

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

        $width = Column::make('width')
            ->label(__('Width'))
            ->value('width')
            ->visible(true)
            ->defaultVisibility(false)
            ->sortable(true);

        $height = Column::make('height')
            ->label(__('Height'))
            ->value('height')
            ->visible(true)
            ->defaultVisibility(false)
            ->sortable(true);

        $duration = Column::make('duration')
            ->label(__('Duration'))
            ->value('duration_formatted')
            ->visible(true)
            ->defaultVisibility(false)
            ->sortable(true);

        $columns->put('basename', $basename);
        $columns->put('size', $size);
        $columns->put('last_modified', $lastModified);
        $columns->put('width', $width);
        $columns->put('height', $height);
        $columns->put('duration', $duration);

        $columns->setPreferred("assets.{$container->handle()}.columns");

        $this->columns = $columns->rejectUnlisted()->values();
    }
}
