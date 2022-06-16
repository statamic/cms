<?php

namespace Statamic\Http\Controllers\CP\API;

use Illuminate\Http\Request;
use Statamic\Facades\Folder;
use Statamic\Http\Controllers\CP\CpController;

class TemplatesController extends CpController
{
    public function index(Request $request)
    {
        $path = $request->folder
            ? 'views/'.ltrim($request->folder)
            : 'views';

        return collect(Folder::disk('resources')->getFilesRecursively($path))
            ->map(function ($view) use ($path) {
                return [
                    'id' => str_replace_first('views/', '', str_before($view, '.')),
                    'title' => str_replace_first($path.'/', '', str_before($view, '.')),
                ];
            });
    }
}
