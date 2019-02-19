<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Statamic\API\Str;
use Statamic\API\Action;
use Illuminate\Http\Request;
use Statamic\API\AssetContainer;
use Illuminate\Http\Resources\Json\Resource;
use Statamic\Http\Controllers\CP\CpController;
use Illuminate\Pagination\LengthAwarePaginator;

class BrowserController extends CpController
{
    public function index()
    {
        $containers = AssetContainer::all();

        // TODO: Filter out unauthorized containers
        // TODO: Handle no authorized containers

        return redirect()
            ->cpRoute('assets.browse.show', $containers->first()->handle());
    }

    public function show($containerHandle, $path = '/')
    {
        // TODO: Handle invalid $container in url
        // TODO: Auth

        $container = AssetContainer::find($containerHandle);

        return view('statamic::assets.browse', [
            'container' => $this->toContainerArray($container),
            'folder' => $path,
            'actions' => Action::for('asset-browser', ['container' => $containerHandle]),
        ]);
    }

    public function folder(Request $request, $container, $path = '/')
    {
        // TODO: Handle invalid $container in url
        // TODO: Auth

        $container = AssetContainer::find($container);

        // Grab all the assets from the container.
        $assets = $container->assets($path);
        $assets = $this->supplementAssetsForDisplay($assets);

        // Get data about the subfolders in the requested asset folder.
        $folders = [];
        foreach ($container->assetFolders($path) as $f) {
            $folders[] = [
                'path' => $f->path(),
                'title' => $f->title()
            ];
        }

        // Set up the paginator, since we don't want to display all the assets.
        // TODO: Instead of manually creating a paginator, get one from the Asset QueryBuilder once it exists.
        $totalAssetCount = $assets->count();
        $perPage = request('perPage');
        $currentPage = (int) $this->request->page ?: 1;
        $offset = ($currentPage - 1) * $perPage;
        $assets = new LengthAwarePaginator(
            $assets->values()->slice($offset, $perPage),
            $totalAssetCount, $perPage, $currentPage
        );

        $assets->each(function($asset) {
            $asset->setSupplement('last_modified_relative', $asset->lastModified()->diffForHumans());
        });

        return Resource::collection($assets)->additional(['meta' => [
            'container' => $this->toContainerArray($container),
            'folders' => $folders,
            'folder' => $container->assetFolder($path)->toArray()
        ]]);
    }

    private function supplementAssetsForDisplay($assets)
    {
        foreach ($assets as &$asset) {
            // Add thumbnails to all image assets.
            if ($asset->isImage()) {
                $asset->setSupplement('thumbnail', $this->thumbnail($asset, 'small'));
                $asset->setSupplement('toenail', $this->thumbnail($asset, 'large'));
            }

            // Set some values for better listing formatting.
            $asset->setSupplement('size_formatted', Str::fileSizeForHumans($asset->size(), 0));
            $asset->setSupplement('last_modified_formatted', $asset->lastModified()->format(config('statamic.cp.date_format')));
        }

        return $assets;
    }

    private function thumbnail($asset, $preset = null)
    {
        return cp_route('assets.thumbnails.show', [
            'asset' => base64_encode($asset->id()),
            'size' => $preset
        ]);
    }

    private function toContainerArray($container)
    {
        return [
            'id' => $container->id(),
            'title' => $container->title(),
            'edit_url' => $container->editUrl()
        ];
    }
}
