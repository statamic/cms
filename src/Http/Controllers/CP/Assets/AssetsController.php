<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Statamic\Assets\AssetUploader;
use Statamic\Assets\UploadedReplacementFile;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;
use Statamic\Exceptions\AuthorizationException;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Resources\CP\Assets\Asset as AssetResource;
use Statamic\Rules\AllowedFile;
use Statamic\Rules\UploadableAssetPath;

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

    public function show(Request $request, $asset)
    {
        $asset = Asset::find(base64_decode($asset));
        $asset = $asset->in($request->site ?? Site::selected()->handle());

        // TODO: Auth

        return new AssetResource($asset);
    }

    public function update(Request $request, $asset)
    {
        $asset = Asset::find(base64_decode($asset));

        $this->authorize('edit', $asset);

        $fields = $asset->blueprint()->fields()->addValues($request->only($request->_localized));

        $fields->validate();

        $values = $fields->process()->values()->filter();

        $localization = $asset->in($request->_locale);

        foreach ($values as $key => $value) {
            $localization->set($key, $value);
        }

        if ($request->focus) {
            $asset->data()->put('focus', $request->focus);
        }

        $asset->save();

        return [
            'success' => true,
            'message' => 'Asset updated',
            'asset' => (new AssetResource($localization))->resolve()['data'],
        ];
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

        $request->validate([
            'file' => array_merge(['file', new AllowedFile], $container->validationRules()),
        ]);

        $file = $request->file('file');
        $folder = $request->folder;

        // Append relative path as subfolder when upload was part of a folder and container allows it
        if ($container->createFolders() && ($relativePath = AssetUploader::getSafePath($request->relativePath))) {
            $folder = rtrim($folder, '/').'/'.$relativePath;
        }

        $basename = $request->option === 'rename' && $request->filename
            ? $request->filename.'.'.$file->getClientOriginalExtension()
            : $file->getClientOriginalName();

        $basename = AssetUploader::getSafeFilename($basename);

        $path = ltrim($folder.'/'.$basename, '/');

        $validator = Validator::make(['path' => $path], ['path' => new UploadableAssetPath($container)]);

        if (! in_array($request->option, ['timestamp', 'overwrite'])) {
            try {
                $validator->validate();
            } catch (ValidationException $e) {
                throw $e->status(409);
            }
        }

        $asset = $container->asset($path) ?? $container->makeAsset($path);

        $asset = $request->option === 'overwrite'
            ? $asset->reupload(new UploadedReplacementFile($file))
            : $asset->upload($file);

        return new AssetResource($asset);
    }

    public function download($asset)
    {
        $asset = Asset::find(base64_decode($asset));

        // TODO: Auth

        return $asset->download();
    }
}
