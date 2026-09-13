<?php

namespace Statamic\Http\Controllers\CP\Assets\Concerns;

use Facades\Statamic\Fields\Validator as FieldValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Statamic\Assets\AssetUploader;
use Statamic\Assets\UploadedReplacementFile;
use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Contracts\Assets\AssetFolder;
use Statamic\Facades\User;
use Statamic\Http\Resources\CP\Assets\Asset as AssetResource;
use Statamic\Rules\AllowedFile;
use Statamic\Rules\UploadableAssetPath;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait FinalizesAssetUploads
{
    protected function finalizeUpload(UploadedFile $file, AssetContainer $container, Request $request): AssetResource
    {
        $validationRules = collect($container->validationRules())
            ->map(fn ($rule) => FieldValidator::parse($rule))
            ->all();

        Validator::make(['file' => $file], [
            'file' => array_merge(['file', new AllowedFile], $validationRules),
        ])->validate();

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

        if (! in_array($request->option, ['timestamp', 'overwrite'])) {
            try {
                Validator::make(['path' => $path], ['path' => new UploadableAssetPath($container)])->validate();
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
}
