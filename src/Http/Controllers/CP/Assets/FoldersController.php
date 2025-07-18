<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Statamic\Assets\AssetUploader;
use Statamic\Facades\Path;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Resources\CP\Assets\Folder;
use Statamic\Rules\AlphaDashSpace;

class FoldersController extends CpController
{
    public function store(Request $request, $container)
    {
        abort_unless($container->createFolders(), 403);

        $request->validate([
            'path' => 'required',
            'directory' => ['required', 'string', new AlphaDashSpace],
        ]);

        $name = AssetUploader::getSafeFilename($request->directory);

        $path = ltrim(Path::assemble($request->path, $name), '/');

        if ($container->disk()->exists($path)) {
            throw ValidationException::withMessages([
                'directory' => __('Directory already exists.'),
            ]);
        }

        if (config('statamic.assets.lowercase')) {
            $path = strtolower($path);
        }

        $folder = $container->assetFolder($path)->save();

        return (new Folder($folder))->resolve();
    }

    public function update(Request $request, $container, $folder)
    {
        $folder = $container->assetFolder($folder)->save();

        return (new Folder($folder))->resolve();
    }
}
