<?php

namespace Statamic\Http\Controllers\CP\API;

use Statamic\API\File;
use Statamic\API\Folder;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class TemplatesController extends CpController
{
    public function index()
    {
        return collect(Folder::disk('resources')->getFilesRecursively('views'))
            ->map(function ($view) {
                return str_replace_first('views/', '',  str_before($view, '.')
            );
        });
    }
}
