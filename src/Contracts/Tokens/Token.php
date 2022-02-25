<?php

namespace Statamic\Contracts\Tokens;

use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface Token
{
    public function token(): string;

    public function handler(): string;

    public function handle($request, Closure $next);

    public function data(): Collection;

    public function get(string $key);

    public function save();

    public function delete();

    public function expiry(): Carbon;

    public function expireAt(Carbon $expiry): self;

    public function hasExpired(): bool;
}
