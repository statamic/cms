<?php

namespace Statamic\CP;

use Statamic\Contracts\Tokens\Token as TokenContract;
use Statamic\Facades\Data;
use Statamic\Facades\Token;
use Statamic\Facades\URL;
use Statamic\Tokens\Handlers\SharedPreview as Handler;

class SharedPreview
{
    public function tokenize($item, ?int $revision = null): TokenContract
    {
        if ($existing = $this->existing($item, $revision)) {
            return $existing;
        }

        $minutes = (int) config('statamic.live_preview.shared_link_expiry', 1440);

        $data = ['reference' => $item->reference()];

        if ($revision) {
            $data['revision'] = $revision;
        }

        return tap(
            Token::make(null, Handler::class, $data)->expireAt(now()->addMinutes($minutes))
        )->save();
    }

    public function existing($item, ?int $revision = null): ?TokenContract
    {
        return Token::all()->first(function ($token) use ($item, $revision) {
            return $token->handler() === Handler::class
                && ! $token->hasExpired()
                && $token->get('reference') === $item->reference()
                && $token->get('revision') == $revision;
        });
    }

    public function item(TokenContract $token)
    {
        $item = Data::find($token->get('reference'));

        if (! $item) {
            return null;
        }

        if ($timestamp = $token->get('revision')) {
            $revision = $this->findRevision($item, $timestamp);

            return $revision ? $item->makeFromRevision($revision) : false;
        }

        if (
            method_exists($item, 'hasWorkingCopy')
            && $item->revisionsEnabled()
            && $item->hasWorkingCopy()
        ) {
            return $item->fromWorkingCopy();
        }

        return $item;
    }

    public function url($item, TokenContract $token, int $target = 0): string
    {
        $preview = $this->item($token);

        if (! $preview) {
            $preview = $item;
        }

        $targets = method_exists($preview, 'previewTargets') ? $preview->previewTargets() : collect();
        $url = URL::makeAbsolute($targets[$target]['url'] ?? $preview->absoluteUrl());

        return $url.(str_contains((string) $url, '?') ? '&' : '?').'token='.$token->token();
    }

    public function findRevision($item, $timestamp)
    {
        if (! method_exists($item, 'revisions')) {
            return null;
        }

        return $item->revisions()->first(
            fn ($revision) => (int) $revision->date()->timestamp === (int) $timestamp
        );
    }
}
