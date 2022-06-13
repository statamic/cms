<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Stache;
use Wilderborn\Partyline\Facade as Partyline;

class StacheWarm extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:stache:warm';
    protected $description = 'Build the "Stache" cache';

    public function handle()
    {
        Partyline::bind($this);

        $this->line('Please wait. This may take a while if you have a lot of content.');

        Stache::warm();

        $this->info('You have poured oil over the Stache and polished it until it shines. It is warm and ready');
    }
}
