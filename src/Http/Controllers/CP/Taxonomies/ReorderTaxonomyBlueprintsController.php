<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Fields\ManagesBlueprints;

class ReorderTaxonomyBlueprintsController extends CpController
{
    use ManagesBlueprints;

    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure fields');
    }

    public function __invoke(Request $request, $taxonomy)
    {
        foreach ($request->order as $index => $handle) {
            $taxonomy->termBlueprint($handle)->setOrder($index + 1)->save();
        }
    }
}
