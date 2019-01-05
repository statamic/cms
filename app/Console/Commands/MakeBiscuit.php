<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;

class MakeBiscuit extends Command
{
    use RunsInPlease;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'statamic:make:biscuit';

    /**
     * Indicates whether the command should be shown in the Artisan command list.
     *
     * @var bool
     */
    protected $hidden = true;

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        while (true) {
            $this->andTheyllSingSingSingSING();
        }
    }

    /**
     * They'll stand close together, with Biscuit chimes ringing.
     *
     * They'll stand hand-in-hand, and those Threes will start singing!
     *
     * @return sing
     */
    private function andTheyllSingSingSingSING()
    {
        $this->line('');
        $this->comment('Fahoo forays,');
        $this->sleep(1300);
        $this->info('Dahoo dorays,');
        $this->sleep(1300);
        $this->comment('Welcome Biscuits,');
        $this->sleep(1300);
        $this->error('Biscuits Day!');
        $this->sleep(1600);

        $this->line('');
        $this->comment('Welcome, welcome,');
        $this->sleep(1300);
        $this->info('Fahoo ramus,');
        $this->sleep(1300);
        $this->comment('Welcome, welcome,');
        $this->sleep(1300);
        $this->info('Dahoo damus,');
        $this->sleep(1300);
        $this->error('Biscuits Day is in our grasp 😲');
        $this->sleep(2000);
        $this->info('So long as we have hands to clasp 👏');
        $this->sleep(2000);
    }

    /**
     * The 404 would rather everyone just do this on Biscuit Day.
     *
     * @param int $milliseconds
     */
    private function sleep($milliseconds)
    {
        usleep(1000 * $milliseconds);
    }
}
