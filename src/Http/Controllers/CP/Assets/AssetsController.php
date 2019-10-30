<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Illuminate\Http\Request;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;
use Statamic\Exceptions\AuthorizationException;
use Statamic\Facades\Action;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class AssetsController extends CpController
{
    use RedirectsToFirstAssetContainer;

    public function index()
    {
        $this->redirectToFirstContainer();

        if (User::current()->can('create', AssetContainerContract::class)) {
            return view('statamic::assets.index');
        }

        throw new AuthorizationException;
    }

    public function show($asset)
    {
        $asset = Asset::find(base64_decode($asset));

        // TODO: Auth

        $asset = $this->supplementAssetForEditing($asset);

        $fields = $asset->blueprint()->fields()
            ->addValues($asset->data()->all())
            ->preProcess();

        return [
            'asset' => $asset->toArray(),
            'container' => $asset->container()->toArray(),
            'values' => $asset->data()->merge($fields->values()),
            'meta' => $fields->meta(),
            'actionUrl' => cp_route('assets.actions'),
        ];
    }

    private function supplementAssetForEditing($asset)
    {
        if ($asset->isImage()) {
            $asset->setSupplement('width', $asset->width());
            $asset->setSupplement('height', $asset->height());

            // Public asset containers can use their regular URLs.
            // Private ones don't have URLs so we'll generate an actual-size "thumbnail".
            $asset->setSupplement('preview', $asset->container()->accessible() ? $asset->url() : $this->thumbnail($asset));
        }

        $asset->setSupplement('last_modified_relative', $asset->lastModified()->diffForHumans());
        $asset->setSupplement('download_url', cp_route('assets.download', base64_encode($asset->id())));

        return $asset;
    }

    public function update(Request $request, $asset)
    {
        $asset = Asset::find(base64_decode($asset));

        // TODO: Auth

        $fields = $asset->blueprint()->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values()->merge([
            'focus' => $request->focus
        ]);

        foreach ($values as $key => $value) {
            $asset->set($key, $value);
        }

        $asset->save();

        if ($asset->isImage()) {
            $asset->setSupplement('thumbnail', $this->thumbnail($asset, 'small'));
            $asset->setSupplement('toenail', $this->thumbnail($asset, 'large'));
        }

        return ['success' => true, 'message' => 'Asset updated', 'asset' => $asset->toArray()];
    }

    public function store(Request $request)
    {
        $request->validate([
            'container' => 'required',
            'folder' => 'required',
        ]);

        $container = AssetContainer::find($request->container);

        abort_unless($container->allowUploads(), 403);

        $this->authorize('store', [AssetContract::class, $container]);

        $file = $request->file('file');
        $path = ltrim($request->folder . '/' . $file->getClientOriginalName(), '/');

        $asset = $container->makeAsset($path)->upload($file);

        if ($asset->isImage()) {
            $asset->setSupplement('thumbnail', $this->thumbnail($asset, 'small'));
            $asset->setSupplement('toenail', $this->thumbnail($asset, 'large'));
        }

        return $asset;
    }

    public function download($asset)
    {
        $asset = Asset::find(base64_decode($asset));

        // TODO: Auth

        $file = $asset->path();

        $filesystem = $asset->disk()->filesystem()->getDriver();
        $stream = $filesystem->readStream($file);

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            "Content-Type" => $filesystem->getMimetype($file),
            "Content-Length" => $filesystem->getSize($file),
            "Content-disposition" => "attachment; filename=\"" . basename($file) . "\"",
        ]);
    }

    public function destroy($asset)
    {
        $asset = Asset::find(base64_decode($asset));

        // TODO: Auth

        $asset->delete();

        return response('', 204);
    }

    private function thumbnail($asset, $preset = null)
    {
        return $asset->thumbnailUrl($preset);
    }
}
