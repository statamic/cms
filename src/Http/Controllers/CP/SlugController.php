<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Support\Str;

class SlugController extends CpController
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'string' => ['required'],
            'separator' => ['required'],
            'language' => ['required'],
            'replacements' => ['nullable', 'array'],
        ]);

        return Str::of($validated['string'])
            ->when($validated['replacements'], function ($string, $replacements) {
                return collect($replacements)->reduce(fn ($string, $replace, $search) => $string->replace($search, $replace), $string);
            })
            ->slug($validated['separator'], $validated['language']);
    }
}
