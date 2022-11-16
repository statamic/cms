<?php

namespace Statamic\Tokens;

use Closure;
use Facades\Statamic\Tokens\Generator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Statamic\Contracts\Tokens\Token as Contract;
use Statamic\Facades\Token;

class AbstractToken implements Contract
{
    protected $token;
    protected $handler;
    protected $data;
    protected $expiry;

    public function __construct(?string $token, string $handler, array $data = [])
    {
        $this->token = $token ?? Generator::generate();
        $this->handler = $handler;
        $this->data = collect($data);
        $this->expiry = Carbon::now()->addHour();
    }

    public function token(): string
    {
        return $this->token;
    }

    public function handler(): string
    {
        return $this->handler;
    }

    public function data(): Collection
    {
        return $this->data;
    }

    public function get(string $key)
    {
        return $this->data->get($key);
    }

    public function save()
    {
        return Token::save($this);
    }

    public function delete()
    {
        return Token::delete($this);
    }

    public function handle($request, Closure $next)
    {
        return app($this->handler)->handle($this, $request, $next);
    }

    public function expiry(): Carbon
    {
        return $this->expiry;
    }

    public function expireAt(Carbon $expiry): self
    {
        $this->expiry = $expiry;

        return $this;
    }

    public function hasExpired(): bool
    {
        return $this->expiry->isPast();
    }
}
