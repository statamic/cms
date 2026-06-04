<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;
use Statamic\Exceptions\AuthorizationException;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\Assets\Concerns\FinalizesAssetUploads;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Resources\CP\Assets\Asset as AssetResource;

class AssetsController extends CpController
{
    use FinalizesAssetUploads, RedirectsToFirstAssetContainer;

    public function index()
    {
        $this->redirectToFirstContainer();

        if (User::current()->can('create', AssetContainerContract::class)) {
            return Inertia::render('assets/Empty', [
                'createUrl' => cp_route('asset-containers.create'),
            ]);
        }

        throw new AuthorizationException;
    }

    public function show($asset)
    {
        $asset = Asset::find(base64_decode($asset));

        abort_if(! $asset, 404);

        $this->authorize('view', $asset);

        return new AssetResource($asset);
    }

    public function update(Request $request, $asset)
    {
        $asset = Asset::find(base64_decode($asset));

        $this->authorize('edit', $asset);

        $fields = $asset->blueprint()->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values()->merge([
            'focus' => $request->focus,
        ]);

        foreach ($values as $key => $value) {
            $asset->set($key, $value);
        }

        $asset->save();

        return [
            'success' => true,
            'message' => 'Asset updated',
            'asset' => (new AssetResource($asset))->resolve()['data'],
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'container' => 'required',
            'folder' => 'required',
        ]);

        $container = AssetContainer::find($request->container);

        throw_unless($container, NotFoundHttpException::class);

        $this->authorize('store', [AssetContract::class, $container]);

        return $this->finalizeUpload($request->file('file'), $container, $request);
    }

    public function download($asset)
    {
        $asset = Asset::find(base64_decode($asset));

        abort_if(! $asset, 404);

        $this->authorize('view', $asset);

        return $asset->download();
    }
}
