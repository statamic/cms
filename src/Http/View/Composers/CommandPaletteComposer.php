<?php

namespace Statamic\Http\View\Composers;

use Illuminate\View\View;
use Statamic\Facades\Blink;
use Statamic\Facades\CommandPalette;

class CommandPaletteComposer
{
    const VIEWS = [
        'statamic::partials.command-palette',
    ];

    public function compose(View $view)
    {
        $view->with('commandPalette', Blink::once('command-palette-composer', fn () => CommandPalette::build()));
    }
}
