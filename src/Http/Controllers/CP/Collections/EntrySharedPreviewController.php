<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Facades\Statamic\CP\SharedPreview;
use Illuminate\Http\Request;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Http\Controllers\CP\CpController;

class EntrySharedPreviewController extends CpController
{
    public function store(Request $request, $collection, $entry)
    {
        $this->authorize('view', $entry);

        $revision = $request->input('revision');

        if ($revision !== null) {
            $revision = (int) $revision;
            throw_unless(SharedPreview::findRevision($entry, $revision), new NotFoundHttpException);
        }

        $token = SharedPreview::tokenize($entry, $revision);
        $url = SharedPreview::url($entry, $token, (int) $request->input('target', 0));

        $minutesRemaining = max(0, now()->diffInMinutes($token->expiry(), false));

        return [
            'url' => $url,
            'expires_in_hours' => max(1, (int) ceil($minutesRemaining / 60)),
        ];
    }
}
