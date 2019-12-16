<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class TermPreviewController extends CpController
{
    public function show()
    {
        return view('statamic::terms.preview');
    }

    public function edit(Request $request, $taxonomy, $term)
    {
        // todo
    }

    public function create(Request $request, $taxonomy, $site)
    {
        // todo
    }
}
