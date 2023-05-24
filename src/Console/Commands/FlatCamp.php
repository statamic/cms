<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;

class FlatCamp extends Command
{
    use RunsInPlease;

    protected $signature = 'flat:camp';
    protected $description = ' Statamic';

    protected $quotes = [
        "No, you're right. Let's do it the dumbest way possible. Because it's easier for you. - Erin",
        'Butter is butter. - Convenience store lady',
        'Christopher Columbus is from Poland. - Krzemo',
        'Is this a safe space? - Jack',
        'Where does Polish come from?" - Erin',
    ];

    public function handle()
    {
        return $this->comment(collect($this->quotes)
            ->map(fn ($quote) => $this->formatForConsole($quote))
            ->random());
    }

    protected function formatForConsole($quote)
    {
        [$text, $author] = str($quote)->explode('-');

        return sprintf(
            "\n  <options=bold>“ %s ”</>\n  <fg=gray>— %s</>\n",
            trim($text),
            trim($author),
        );
    }
}
