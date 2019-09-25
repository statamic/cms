<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Statamic\Facades\User;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class PublishedTermsController extends CpController
{
    public function store(Request $request, $taxonomy, $term)
    {
        $this->authorize('publish', $taxonomy);

        $term = $term->publish([
            'message' => $request->message,
            'user' => User::fromUser($request->user()),
        ]);

        return $term->toArray();
    }

    public function destroy(Request $request, $taxonomy, $term)
    {
        $this->authorize('publish', $taxonomy);

        $term->unpublish([
            'message' => $request->message,
            'user' => User::fromUser($request->user()),
        ]);
    }
}
