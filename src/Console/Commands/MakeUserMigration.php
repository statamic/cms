<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\File;

class MakeUserMigration extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:make:user-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Makes the user migration file';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        File::put(
            database_path().'/migrations/'.date('Y_m_d_His').'_create_users_tables.php',
            File::get(__DIR__.'/stubs/create_users_tables.php.stub')
        );
    }
}
