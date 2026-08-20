<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\Facades\Token;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Tokens\Handlers\SharedPreview;

class EntrySharedPreviewController extends CpController
{
    public function store($collection, $entry)
    {
        $this->authorize('view', $entry);

        $minutes = (int) config('statamic.live_preview.shared_link_expiry', 1440);

        $token = tap(
            Token::make(null, SharedPreview::class, [
                'reference' => $entry->reference(),
            ])->expireAt(now()->addMinutes($minutes))
        )->save();

        $url = $entry->absoluteUrl();

        return [
            'url' => $url.(str_contains($url, '?') ? '&' : '?').'token='.$token->token(),
            'expires_in_hours' => (int) ceil($minutes / 60),
        ];
    }
}
