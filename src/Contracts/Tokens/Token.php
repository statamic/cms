<?php

namespace Statamic\Contracts\Tokens;

use Illuminate\Support\Collection;

interface Token
{
    public function token(): string;

    public function handler(): string;

    public function handle(): bool;

    public function data(): Collection;

    public function get(string $key);
}
