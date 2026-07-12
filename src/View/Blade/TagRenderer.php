<?php

namespace Statamic\View\Blade;

use Illuminate\Support\Facades\Blade;
use Statamic\Contracts\View\TagRenderer as TagRendererContract;

class TagRenderer implements TagRendererContract
{
    public function render(string $contents, array $data): string
    {
        return Blade::render($contents, $data);
    }

    public function getLanguage(): string
    {
        return 'blade';
    }
}
