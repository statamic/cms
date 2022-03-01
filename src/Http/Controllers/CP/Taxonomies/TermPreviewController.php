<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Illuminate\Http\Request;
use Statamic\Contracts\Taxonomies\Term as TermContract;
use Statamic\Facades\Term;
use Statamic\Http\Controllers\CP\PreviewController;

class TermPreviewController extends PreviewController
{
    public function create(Request $request, $taxonomy, $site)
    {
        $this->authorize('create', [TermContract::class, $taxonomy]);

        $fields = $taxonomy->termBlueprint($request->blueprint)
            ->fields()
            ->addValues($preview = $request->preview)
            ->process();

        $values = array_except($fields->values()->all(), ['slug']);

        $term = Term::make()
            ->slug($preview['slug'] ?? 'slug')
            ->taxonomy($taxonomy)
            ->in($site->handle())
            ->data($values);

        return $this->tokenizeAndReturn($request, $term);
    }
}
