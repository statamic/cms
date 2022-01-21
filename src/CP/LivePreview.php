<?php

namespace Statamic\CP;

use Illuminate\Support\Facades\Cache;
use Statamic\Contracts\Tokens\Token as TokenContract;
use Statamic\Facades\Token;
use Statamic\Tokens\Handlers\LivePreviewEntry;

class LivePreview
{
    public function tokenize($token, $item): TokenContract
    {
        $token = tap(Token::make($token, LivePreviewEntry::class))->save();

        Cache::put('statamic.live-preview.'.$token->token(), $item, now()->addHour());

        return $token;
    }
}
