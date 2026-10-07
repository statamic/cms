<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Facades\Statamic\Fields\Validator as FieldValidator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Statamic\Assets\AssetUploader;
use Statamic\Assets\UploadedReplacementFile;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;
use Statamic\Contracts\Assets\AssetFolder;
use Statamic\CP\Assets\CropProcessor;
use Statamic\Exceptions\AuthorizationException;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Resources\CP\Assets\Asset as AssetResource;
use Statamic\Rules\AllowedFile;
use Statamic\Rules\UploadableAssetPath;

use function Statamic\trans as __;

class AssetsController extends CpController
{
    use RedirectsToFirstAssetContainer;

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

        $validationRules = collect($container->validationRules())
            ->map(fn ($rule) => FieldValidator::parse($rule))
            ->all();

        $request->validate([
            'file' => array_merge(['file', new AllowedFile], $validationRules),
        ]);

        $file = $request->file('file');
        $folder = $request->folder;

        // Append relative path as subfolder when upload was part of a folder and user is allowed to create folders
        if (User::current()->can('create', [AssetFolder::class, $container]) && ($relativePath = AssetUploader::getSafePath($request->relativePath))) {
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

    public function crop(Request $request, $encodedAsset)
    {
        $asset = Asset::find(base64_decode($encodedAsset));

        abort_if(! $asset, 404);

        $this->authorize('view', $asset);

        abort_unless($asset->isImage(), 422, __('The asset is not an image.'));

        $request->validate([
            'x' => 'required|integer|min:0',
            'y' => 'required|integer|min:0',
            'width' => 'required|integer|min:1',
            'height' => 'required|integer|min:1',
            'quality' => 'nullable|integer|min:1|max:100',
            'format' => 'nullable|in:jpg,jpeg,png,webp,gif,avif',
            'background' => 'nullable|in:black,white',
            'replace' => 'boolean',
        ]);

        $replace = $request->boolean('replace');
        $extension = strtolower($request->input('format') ?: $asset->extension());

        // Changing the format produces a different file extension, which can't
        // overwrite the original. It can only be saved as a new copy.
        abort_if($replace && $this->normalizeExtension($extension) !== $this->normalizeExtension($asset->extension()), 422, __('statamic::messages.crop_replace_unavailable_format'));

        $replace
            ? $this->authorize('reupload', $asset)
            : $this->authorize('store', [AssetContract::class, $asset->container()]);

        $contents = app(CropProcessor::class)->crop(
            $asset->contents(),
            $extension,
            $request->integer('x'),
            $request->integer('y'),
            $request->integer('width'),
            $request->integer('height'),
            $request->filled('quality') ? $request->integer('quality') : null,
            $request->input('background') === 'black' ? '000000' : 'ffffff',
        );

        // When replacing, reuse the original basename verbatim so the extension
        // matches exactly (Asset::extension() isn't lowercased, and ".jpeg" stays
        // ".jpeg"), otherwise reupload() throws a FileExtensionMismatch.
        $basename = $replace
            ? $asset->basename()
            : pathinfo($asset->basename(), PATHINFO_FILENAME).'.'.$extension;

        $tempPath = tempnam(sys_get_temp_dir(), 'statamic-crop');
        file_put_contents($tempPath, $contents);

        $file = new UploadedFile($tempPath, $basename, test: true);

        try {
            $this->validateCroppedFile($file, $asset->container());

            if ($replace) {
                $asset->reupload(new UploadedReplacementFile($file));
            } else {
                $path = ltrim($asset->folder().'/'.$basename, '/');
                $asset = $asset->container()->makeAsset($path)->upload($file);
            }
        } finally {
            @unlink($tempPath);
        }

        return new AssetResource($asset);
    }

    private function validateCroppedFile(UploadedFile $file, AssetContainerContract $container): void
    {
        $rules = collect($container->validationRules())
            ->map(fn ($rule) => FieldValidator::parse($rule))
            ->all();

        Validator::make(
            ['file' => $file],
            ['file' => array_merge(['file', new AllowedFile], $rules)]
        )->validate();
    }

    private function normalizeExtension($extension)
    {
        $extension = strtolower($extension);

        return $extension === 'jpeg' ? 'jpg' : $extension;
    }

    public function download($asset)
    {
        $asset = Asset::find(base64_decode($asset));

        abort_if(! $asset, 404);

        $this->authorize('view', $asset);

        return $asset->download();
    }
}
