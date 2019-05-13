<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Statamic\Http\Controllers\CP\CpController;

class TaxonomiesController extends CpController
{
    public function index()
    {
        return view('statamic::taxonomies.index', [
            'taxonomies' => collect()
        ]);
    }

    public function create()
    {
        return view('statamic::taxonomies.create');
    }
}
