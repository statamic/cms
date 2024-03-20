<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Stache;

use function Laravel\Prompts\spin;

class StacheWarm extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:stache:warm';
    protected $description = 'Build the "Stache" cache';

    public function handle()
    {
        spin(callback: fn () => Stache::warm(), message: 'Warming the Stache...');

        $this->components->info('You have poured oil over the Stache and polished it until it shines. It is warm and ready');
    }
}
