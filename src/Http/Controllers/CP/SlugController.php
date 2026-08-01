<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Support\Str;

class SlugController extends CpController
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'string' => ['required'],
            'separator' => ['required'],
            'language' => ['required'],
            'preserve_paths' => ['sometimes', 'boolean'],
        ]);

        if ($request->boolean('preserve_paths')) {
            return collect(explode('/', $data['string']))
                ->map(function ($segment) use ($data) {
                    if ($segment === '_index') {
                        return '_index';
                    }

                    return Str::slug($segment, $data['separator'], $data['language']);
                })
                ->filter()
                ->implode('/');
        }

        return Str::slug($data['string'], $data['separator'], $data['language']);
    }
}
