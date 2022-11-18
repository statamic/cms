<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Statamic\Facades\Path;
use Statamic\Http\Controllers\CP\CpController;

class FoldersController extends CpController
{
    public function store(Request $request, $container)
    {
        abort_unless($container->createFolders(), 403);

        $request->validate([
            'path' => 'required',
            'directory' => 'required|alpha_dash',
        ]);

        $path = ltrim(Path::assemble($request->path, $request->directory), '/');

        if ($container->disk()->exists($path)) {
            throw ValidationException::withMessages([
                'directory' => __('Directory already exists.'),
            ]);
        }

        if (config('statamic.assets.lowercase')) {
            $path = strtolower($path);
        }

        return $container->assetFolder($path)->save();
    }

    public function update(Request $request, $container, $folder)
    {
        return $container->assetFolder($folder)->save();
    }
}
