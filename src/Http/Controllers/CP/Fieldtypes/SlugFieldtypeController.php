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
            'text' => ['required'],
            'glue' => ['required'],
            'language' => ['required'],
        ]);

        $slug = Str::slug($validated['text'], $validated['glue'], $validated['language']);

        return [
            'slug' => $slug,
        ];
    }
}
