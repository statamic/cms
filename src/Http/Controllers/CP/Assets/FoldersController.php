<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Statamic\Facades\Path;
use Illuminate\Http\Request;
use Statamic\Facades\AssetContainer;
use Statamic\Http\Controllers\CP\CpController;
use Illuminate\Validation\ValidationException;

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

        $path = strtolower($path); // Prevent case sensitivity collisions

        return $container->assetFolder($path)->save();
    }

    public function update(Request $request, $container, $folder)
    {
        return $container->assetFolder($folder)->save();
    }
}
