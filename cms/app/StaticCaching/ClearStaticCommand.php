<?php

namespace Statamic\StaticCaching;

use Illuminate\Console\Command;

class ClearStaticCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'clear:static';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the Static Page Cache.';

    /**
     * @var \Statamic\StaticCaching\Cacher
     */
    private $cacher;

    /**
     * @param \Statamic\StaticCaching\Cacher $cacher
     */
    public function __construct(Cacher $cacher)
    {
        parent::__construct();

        $this->cacher = $cacher;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->cacher->flush();

        $this->info('Your static page cache is now so very, very empty.');
    }
}
