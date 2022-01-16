<?php

namespace Statamic\Tokens;

use Facades\Statamic\Tokens\Generator;
use Illuminate\Support\Collection;
use Statamic\Contracts\Tokens\Token as Contract;
use Statamic\Facades\Token;

class AbstractToken implements Contract
{
    protected $token;
    protected $handler;
    protected $data;

    public function __construct(?string $token, string $handler, array $data = [])
    {
        $this->token = $token ?? Generator::generate();
        $this->handler = $handler;
        $this->data = collect($data);
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

    public function handle(): bool
    {
        app($this->handler)->handle($this);

        return true;
    }
}
