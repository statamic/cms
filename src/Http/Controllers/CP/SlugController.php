<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Support\Str;

class SlugController extends CpController
{
    public function __invoke(Request $request)
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
