<?php

namespace Statamic\Tokens\Handlers;

use Statamic\Facades\Entry;

class LivePreviewEntry
{
    public function handle($token)
    {
        $entry = Entry::find($token->get('entry'));

        foreach ($token->get('data') as $key => $value) {
            $entry->setSupplement($key, $value);
        }

        Entry::substitute($entry);
    }
}
