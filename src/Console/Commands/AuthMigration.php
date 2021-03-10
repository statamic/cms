<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\File;

class AuthMigration extends Command
{
    use RunsInPlease;

    protected $composer;
    protected $name = 'statamic:auth:migration';
    protected $description = 'Generate Auth Migrations';

    public function __construct(Composer $composer)
    {
        parent::__construct();

        $this->composer = $composer;
    }

    public function handle()
    {
        $from = __DIR__.'/stubs/auth/statamic_auth_tables.php.stub';
        $file = date('Y_m_d_His', time()).'_statamic_auth_tables';
        $to = database_path("migrations/{$file}.php");

        $contents = File::get($from);

        if (config('statamic.users.tables.users', 'users') === 'users') {
            $contents = str_replace('USERS_TABLE_REPLACE', File::get(__DIR__.'/stubs/auth/update_users_table.php.stub'), $contents);
        } else {
            $contents = str_replace('USERS_TABLE_REPLACE', File::get(__DIR__.'/stubs/auth/create_users_table.php.stub'), $contents);
        }

        $contents = str_replace('USERS_TABLE', config('statamic.users.tables.users', 'users'), $contents);
        $contents = str_replace('ROLE_USER_TABLE', config('statamic.users.tables.role_user', 'role_user'), $contents);
        $contents = str_replace('GROUP_USER_TABLE', config('statamic.users.tables.group_user', 'group_user'), $contents);

        File::put($to, $contents);

        $this->line("<info>Created Migration:</info> {$file}");

        $this->composer->dumpAutoloads();
    }
}
