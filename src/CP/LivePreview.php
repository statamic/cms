<?php

namespace Statamic\CP;

use Illuminate\Support\Facades\Cache;
use Statamic\Contracts\Tokens\Token as TokenContract;
use Statamic\Facades\Token;
use Statamic\Tokens\Handlers\LivePreview as Handler;

class LivePreview
{
    public function tokenize($token, $item): TokenContract
    {
        $token = tap(Token::make($token, Handler::class))->save();

        Cache::put('statamic.live-preview.'.$token->token(), $item, now()->addHour());

        return $token;
    }

    public function item(TokenContract $token)
    {
        return Cache::get('statamic.live-preview.'.$token->token());
    }
}
