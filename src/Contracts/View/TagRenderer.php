<?php

namespace Statamic\Contracts\View;

interface TagRenderer
{
    public function getLanguage(): string;

    public function render(string $contents, array $data): string;
}
