<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Support\Str;

class SlugController extends CpController
{
    public function __invoke(Request $request)
    {
        return Str::slug(...$request->validate([
            'string' => ['required'],
            'separator' => ['required'],
            'language' => ['required'],
        ]));
    }
}
