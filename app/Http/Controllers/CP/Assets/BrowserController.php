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

        if ($containers->isEmpty()) {
            return view('statamic::assets.index');
        }

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

        $paginator = $container
            ->queryAssets()
            ->where('folder', $path)
            ->orderBy($request->sort, $request->order)
            ->paginate(15);

        $this->supplementAssetsForDisplay($paginator->getCollection());

        return Resource::collection($paginator)->additional(['meta' => [
            'container' => $this->toContainerArray($container),
            'folders' => $container->assetFolders($path)->values()->toArray(),
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
            $asset->setSupplement('last_modified_relative', $asset->lastModified()->diffForHumans());
        }

        return $assets;
    }

    private function thumbnail($asset, $preset = null)
    {
        return $asset->thumbnailUrl($preset);
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
