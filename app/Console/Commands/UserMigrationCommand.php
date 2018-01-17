<?php

namespace Statamic\Console\Commands;

use Statamic\API\File;
use Illuminate\Console\Command;

class UserMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:user-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Makes the user migration file.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        File::put(
            database_path().'/migrations/'.date('Y_m_d_His') . '_create_users_tables.php',
            File::get(__DIR__.'/stubs/create_users_tables.php.stub')
        );
    }
}
