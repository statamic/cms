<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Resources\CP\Taxonomies\Term as TermResource;

class PublishedTermsController extends CpController
{
    public function store(Request $request, $taxonomy, $term)
    {
        $this->authorize('publish', $taxonomy);

        $term = $term->publish([
            'message' => $request->message,
            'user' => User::fromUser($request->user()),
        ]);

        return new TermResource($term);
    }

    public function destroy(Request $request, $taxonomy, $term)
    {
        $this->authorize('publish', $taxonomy);

        $term = $term->unpublish([
            'message' => $request->message,
            'user' => User::fromUser($request->user()),
        ]);

        return new TermResource($term);
    }
}
