<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Illuminate\Http\Request;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;
use Statamic\Exceptions\AuthorizationException;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Resources\CP\Assets\Asset as AssetResource;

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
            'file' => ['file', function ($attribute, $value, $fail) {
                if (in_array(trim(strtolower($value->getClientOriginalExtension())), ['php', 'php3', 'php4', 'php5', 'phtml'])) {
                    $fail(__('validation.uploaded'));
                }
            }],
        ]);

        $container = AssetContainer::find($request->container);

        abort_unless($container->allowUploads(), 403);

        $this->authorize('store', [AssetContract::class, $container]);

        $file = $request->file('file');
        $path = ltrim($request->folder.'/'.$file->getClientOriginalName(), '/');

        $asset = $container->makeAsset($path)->upload($file);

        return new AssetResource($asset);
    }

    public function download($asset)
    {
        $asset = Asset::find(base64_decode($asset));

        // TODO: Auth

        return $asset->download();
    }

    public function destroy($asset)
    {
        $asset = Asset::find(base64_decode($asset));

        // TODO: Auth

        $asset->delete();

        return response('', 204);
    }
}
