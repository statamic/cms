<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Support\Facades\File;
use Inertia\Inertia;

class PlaygroundController extends CpController
{
    public function __invoke()
    {
        $icons = collect(File::files(statamic_path('packages/ui/icons')))->map(function ($file) {
            return $file->getFilenameWithoutExtension();
        })->all();

        return Inertia::render('Playground', [
            'icons' => $icons,
        ]);
    }
}
