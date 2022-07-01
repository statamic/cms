<?php

namespace Statamic\Http\Controllers\CP\API;

use Statamic\Facades\Folder;
use Statamic\Http\Controllers\CP\CpController;

class TemplatesController extends CpController
{
    public function index()
    {
        return collect(Folder::disk('resources')->getFilesRecursively('views'))
            ->map(function ($view) {
                return str_replace_first('views/', '', str_before($view, '.'));
            })
            ->filter()->values();
    }
}
