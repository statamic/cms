<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Str;

class SlugFieldtypeController extends CpController
{
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'from' => ['required'],
            'separator' => ['required'],
            'language' => ['required'],
        ]);

        $slug = Str::slug($validated['from'], $validated['separator'], $validated['language']);

        return [
            'slug' => $slug,
        ];
    }
}
